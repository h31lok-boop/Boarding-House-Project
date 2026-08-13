<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class PaymentWordDocumentService
{
    /**
     * Build a genuine Office Open XML (.docx) payment document.
     *
     * @return array{path: string, filename: string, document_number: string, is_paid: bool}
     */
    public function create(Payment $payment): array
    {
        $isPaid = strtolower((string) $payment->status) === 'paid';
        $documentNumber = $payment->receipt_number
            ?: 'PAY-'.now()->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $documentType = $isPaid ? 'Payment Receipt' : 'Payment Statement';
        $tenantName = $payment->tenant?->user?->name ?: 'Tenant';
        $tenantEmail = $payment->tenant?->user?->email ?: 'Not provided';
        $house = $payment->boardingHouse;
        $houseName = $house?->getRawOriginal('name') ?: 'Boarding house';
        $houseAddress = $house?->getRawOriginal('full_address')
            ?: ($house?->getRawOriginal('address') ?: 'Address not provided');
        $status = Str::headline((string) ($payment->status ?: 'pending'));
        $method = Str::headline((string) ($payment->payment_method ?: 'cash'));
        $reference = $payment->reference_number ?: ($payment->reference_no ?: 'Not provided');
        $recordedAt = $payment->getRawOriginal('paid_at') ?: $payment->getRawOriginal('created_at');
        $dateLabel = $isPaid ? 'Payment date' : 'Recorded date';
        $amountLabel = $isPaid ? 'Total Paid' : 'Amount Due';
        $generatedAt = now();

        $documentXml = $this->documentXml([
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'tenant_name' => $tenantName,
            'tenant_email' => $tenantEmail,
            'house_name' => $houseName,
            'house_address' => $houseAddress,
            'status' => $status,
            'method' => $method,
            'reference' => $reference,
            'due_date' => $this->date($payment->getRawOriginal('due_date'), 'Not set'),
            'date_label' => $dateLabel,
            'recorded_at' => $this->dateTime($recordedAt, 'Not recorded'),
            'amount_label' => $amountLabel,
            'amount' => 'PHP '.number_format((float) $payment->amount, 2),
            'notes' => filled($payment->notes) ? (string) $payment->notes : 'No notes provided.',
            'generated_at' => $generatedAt->format('F d, Y h:i A'),
            'notice' => $isPaid
                ? 'Keep this receipt for your records.'
                : 'This statement is not proof of payment until its status is marked Paid.',
        ]);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'boardmatch-payment-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary payment document.');
        }

        $zip = new ZipArchive;
        $opened = $zip->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            @unlink($temporaryPath);
            throw new RuntimeException('Unable to create the Word payment document.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $zip->addFromString('docProps/core.xml', $this->corePropertiesXml($documentType, $documentNumber, $generatedAt));
            $zip->addFromString('docProps/app.xml', $this->appPropertiesXml());
            $zip->addFromString('word/document.xml', $documentXml);
            $zip->addFromString('word/styles.xml', $this->stylesXml());
            $zip->addFromString('word/settings.xml', $this->settingsXml());
            $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelationshipsXml());
        } finally {
            $zip->close();
        }

        $safeNumber = Str::slug($documentNumber) ?: 'payment-'.$payment->id;

        return [
            'path' => $temporaryPath,
            'filename' => $safeNumber.'-'.Str::slug($documentType).'.docx',
            'document_number' => $documentNumber,
            'is_paid' => $isPaid,
        ];
    }

    private function documentXml(array $data): string
    {
        $details = [
            ['Payment status', $data['status']],
            ['Payment method', $data['method']],
            ['Reference number', $data['reference']],
            ['Due date', $data['due_date']],
            [$data['date_label'], $data['recorded_at']],
        ];

        $detailRows = collect($details)
            ->map(fn (array $row) => $this->tableRow($row[0], $row[1]))
            ->implode('');

        $notes = $this->paragraph($data['notes'], size: 20, color: '475569', after: 80);
        $notice = $this->paragraph($data['notice'], size: 18, color: '64748B', italic: true, after: 0);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<w:body>'
            .$this->paragraph('BoardMatch', 'Title', size: 34, color: '1D4ED8', bold: true, after: 20)
            .$this->paragraph('Boarding House Management System', size: 18, color: '64748B', after: 360)
            .$this->paragraph('OFFICIAL PAYMENT DOCUMENT', size: 18, color: '2563EB', bold: true, after: 80, caps: true)
            .$this->paragraph($data['document_type'], 'Heading1', size: 40, color: '0F172A', bold: true, after: 260)
            .$this->twoColumnTable(
                'Document number', $data['document_number'],
                'Status', $data['status']
            )
            .$this->spacer(240)
            .$this->twoColumnTable(
                'ISSUED TO', $data['tenant_name']."\n".$data['tenant_email'],
                'BOARDING HOUSE', $data['house_name']."\n".$data['house_address']
            )
            .$this->spacer(280)
            .'<w:tbl>'.$this->tableProperties().$detailRows
            .$this->tableRow($data['amount_label'], $data['amount'], 'DBEAFE', true, 28, '1D4ED8')
            .'</w:tbl>'
            .$this->spacer(280)
            .$this->paragraph('NOTES', size: 18, color: '64748B', bold: true, after: 90, caps: true)
            .$notes
            .$this->spacer(620)
            .$this->signatureTable($data['tenant_name'], 'Authorized Representative')
            .$this->spacer(360)
            .$this->paragraph('Generated by BoardMatch on '.$data['generated_at'].'.', size: 18, color: '64748B', after: 70)
            .$notice
            .'<w:sectPr>'
            .'<w:pgSz w:w="11906" w:h="16838"/>'
            .'<w:pgMar w:top="1000" w:right="1050" w:bottom="1000" w:left="1050" w:header="450" w:footer="450" w:gutter="0"/>'
            .'<w:cols w:space="720"/>'
            .'<w:docGrid w:linePitch="360"/>'
            .'</w:sectPr>'
            .'</w:body></w:document>';
    }

    private function paragraph(
        string $text,
        ?string $style = null,
        int $size = 22,
        string $color = '0F172A',
        bool $bold = false,
        bool $italic = false,
        int $after = 120,
        string $align = 'left',
        bool $caps = false,
    ): string {
        $paragraphProperties = '<w:pPr>'
            .($style ? '<w:pStyle w:val="'.$this->xml($style).'"/>' : '')
            .'<w:jc w:val="'.$this->xml($align).'"/>'
            .'<w:spacing w:after="'.$after.'" w:line="276" w:lineRule="auto"/>'
            .'</w:pPr>';
        $runProperties = '<w:rPr>'
            .'<w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/>'
            .'<w:color w:val="'.$this->xml($color).'"/>'
            .'<w:sz w:val="'.$size.'"/><w:szCs w:val="'.$size.'"/>'
            .($bold ? '<w:b/><w:bCs/>' : '')
            .($italic ? '<w:i/><w:iCs/>' : '')
            .($caps ? '<w:caps/>' : '')
            .'</w:rPr>';

        $lines = explode("\n", $text);
        $runs = collect($lines)->map(function (string $line, int $index) use ($runProperties): string {
            return ($index > 0 ? '<w:r><w:br/></w:r>' : '')
                .'<w:r>'.$runProperties.'<w:t xml:space="preserve">'.$this->xml($line).'</w:t></w:r>';
        })->implode('');

        return '<w:p>'.$paragraphProperties.$runs.'</w:p>';
    }

    private function spacer(int $after): string
    {
        return '<w:p><w:pPr><w:spacing w:after="'.$after.'"/></w:pPr></w:p>';
    }

    private function tableProperties(): string
    {
        return '<w:tblPr>'
            .'<w:tblW w:w="0" w:type="auto"/>'
            .'<w:tblBorders>'
            .'<w:top w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'<w:left w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'<w:bottom w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'<w:right w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'<w:insideH w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'<w:insideV w:val="single" w:sz="6" w:color="E2E8F0"/>'
            .'</w:tblBorders>'
            .'<w:tblCellMar><w:top w:w="130" w:type="dxa"/><w:left w:w="180" w:type="dxa"/><w:bottom w:w="130" w:type="dxa"/><w:right w:w="180" w:type="dxa"/></w:tblCellMar>'
            .'</w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="3700"/><w:gridCol w:w="5700"/></w:tblGrid>';
    }

    private function tableRow(string $label, string $value, ?string $fill = null, bool $boldValue = true, int $valueSize = 22, string $valueColor = '0F172A'): string
    {
        return '<w:tr>'
            .$this->cell($label, 3700, $fill, 20, '64748B')
            .$this->cell($value, 5700, $fill, $valueSize, $valueColor, $boldValue, 'right')
            .'</w:tr>';
    }

    private function twoColumnTable(string $labelOne, string $valueOne, string $labelTwo, string $valueTwo): string
    {
        return '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders>'
            .'<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/>'
            .'</w:tblBorders><w:tblCellMar><w:top w:w="60" w:type="dxa"/><w:left w:w="0" w:type="dxa"/><w:bottom w:w="60" w:type="dxa"/><w:right w:w="220" w:type="dxa"/></w:tblCellMar></w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="4700"/><w:gridCol w:w="4700"/></w:tblGrid>'
            .'<w:tr>'
            .$this->labeledCell($labelOne, $valueOne, 4700)
            .$this->labeledCell($labelTwo, $valueTwo, 4700)
            .'</w:tr></w:tbl>';
    }

    private function labeledCell(string $label, string $value, int $width): string
    {
        return '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/><w:vAlign w:val="top"/></w:tcPr>'
            .$this->paragraph($label, size: 17, color: '64748B', bold: true, after: 80, caps: true)
            .$this->paragraph($value, size: 22, color: '0F172A', bold: true, after: 0)
            .'</w:tc>';
    }

    private function cell(string $text, int $width, ?string $fill, int $size, string $color, bool $bold = false, string $align = 'left'): string
    {
        return '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'
            .($fill ? '<w:shd w:val="clear" w:color="auto" w:fill="'.$this->xml($fill).'"/>' : '')
            .'<w:vAlign w:val="center"/></w:tcPr>'
            .$this->paragraph($text, size: $size, color: $color, bold: $bold, after: 0, align: $align)
            .'</w:tc>';
    }

    private function signatureTable(string $tenantName, string $representative): string
    {
        return '<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders>'
            .'<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/>'
            .'</w:tblBorders><w:tblCellMar><w:top w:w="40" w:type="dxa"/><w:left w:w="180" w:type="dxa"/><w:bottom w:w="40" w:type="dxa"/><w:right w:w="180" w:type="dxa"/></w:tblCellMar></w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="4700"/><w:gridCol w:w="4700"/></w:tblGrid><w:tr>'
            .$this->signatureCell($tenantName, 'Tenant acknowledgment')
            .$this->signatureCell($representative, 'Boarding house / BoardMatch')
            .'</w:tr></w:tbl>';
    }

    private function signatureCell(string $name, string $label): string
    {
        return '<w:tc><w:tcPr><w:tcW w:w="4700" w:type="dxa"/><w:tcBorders><w:top w:val="single" w:sz="8" w:color="64748B"/></w:tcBorders></w:tcPr>'
            .$this->paragraph($name, size: 20, color: '0F172A', bold: true, after: 20, align: 'center')
            .$this->paragraph($label, size: 17, color: '64748B', after: 0, align: 'center')
            .'</w:tc>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function documentRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="0F172A"/></w:rPr></w:rPrDefault>'
            .'<w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/></w:style>'
            .'</w:styles>';
    }

    private function settingsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:zoom w:percent="100"/><w:defaultTabStop w:val="720"/><w:compat/>'
            .'</w:settings>';
    }

    private function corePropertiesXml(string $documentType, string $documentNumber, Carbon $generatedAt): string
    {
        $timestamp = $generatedAt->copy()->utc()->format('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            .'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            .'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:title>'.$this->xml($documentType.' '.$documentNumber).'</dc:title>'
            .'<dc:subject>BoardMatch payment document</dc:subject><dc:creator>BoardMatch</dc:creator>'
            .'<cp:lastModifiedBy>BoardMatch</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:created>'
            .'<dcterms:modified xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:modified>'
            .'</cp:coreProperties>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            .'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>BoardMatch</Application><AppVersion>1.0</AppVersion>'
            .'</Properties>';
    }

    private function date($value, string $fallback): string
    {
        if (! $value) {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->format('F d, Y');
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function dateTime($value, string $fallback): string
    {
        if (! $value) {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->format('F d, Y h:i A');
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}

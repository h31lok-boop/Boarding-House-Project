<?php

test('all workspace modal styles use a viewport-level isolated foundation', function () {
    $projectRoot = dirname(__DIR__, 2);
    $javascript = file_get_contents($projectRoot.'/resources/js/app.js');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');
    $payments = file_get_contents($projectRoot.'/resources/views/admin/payments.blade.php');

    expect($javascript)
        ->toContain("'.fixed.inset-0'")
        ->toContain('el instanceof HTMLDialogElement && !el.open')
        ->toContain('&& !(el instanceof HTMLDialogElement && el.open)')
        ->toContain('mountModalAtDocumentLevel')
        ->toContain('resetModalScroll')
        ->toContain("document.body.appendChild(modal)")
        ->toContain("modal.setAttribute('data-modal-portaled', 'true')")
        ->toContain("document.documentElement.classList.toggle('modal-open'")
        ->toContain("document.body.classList.toggle('modal-open'");

    expect($css)
        ->toContain("body.modal-open [data-modal-active='true']:not(.bm-native-dialog)")
        ->toContain("body.modal-open .bm-native-dialog[open][data-modal-active='true']")
        ->toContain('.bm-native-dialog:not([open])')
        ->toContain("body.modal-open [data-modal-active='true'][data-modal-portaled='true']")
        ->toContain('max-height: calc(100dvh - clamp(1.3rem, 4vw, 3rem)) !important')
        ->toContain('z-index: 2147483647 !important')
        ->toContain('.bm-modal__footer-group')
        ->toContain('overflow: hidden !important');

    expect($payments)
        ->toContain('aria-labelledby="record-payment-title"')
        ->toContain('aria-labelledby="payment-details-title"')
        ->toContain('class="bm-modal-overlay"')
        ->toContain('class="bm-modal bm-modal--lg"')
        ->toContain('class="bm-modal__header"')
        ->toContain('class="bm-modal__body"')
        ->toContain('class="bm-modal__footer"');
});

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class BoardingHousePolicyController extends Controller
{
    /**
     * Display the policy editor for a given locale.
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale', App::getLocale());
        $categories = Lang::get('boarding_house_policies.categories', [], $locale);

        return view('admin.boarding-house-policies.index', [
            'locale' => $locale,
            'categories' => $categories,
        ]);
    }

    /**
     * Persist the policy categories back to the translation file.
     */
    public function update(Request $request)
    {
        $locale = $request->input('locale', App::getLocale());
        $raw = $request->input('categories_json', '[]');

        try {
            $categories = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return back()
                ->withErrors(['categories_json' => "Invalid JSON: {$e->getMessage()}"])
                ->withInput();
        }

        if (!is_array($categories)) {
            return back()
                ->withErrors(['categories_json' => 'The top-level structure must be an array of categories.'])
                ->withInput();
        }

        $directory = resource_path("lang/$locale");
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = self::buildTranslationFileContent($categories);
        File::put("$directory/boarding_house_policies.php", $content);

        return redirect()
            ->route('admin.boarding-house-policies.index', ['locale' => $locale])
            ->with('status', "Boarding house policies updated for locale '{$locale}'.");
    }

    private static function buildTranslationFileContent(array $categories): string
    {
        $export = var_export($categories, true);
        $export = str_replace("\n", "\n    ", $export);

        return <<<PHP
<?php

return [
    'categories' => $export,
];
PHP;
    }
}

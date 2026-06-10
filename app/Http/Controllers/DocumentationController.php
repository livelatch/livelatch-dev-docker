<?php

namespace App\Http\Controllers;

use App\Services\DocumentationLibrary;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    public function __construct(private DocumentationLibrary $library)
    {
    }

    public function index(Request $request)
    {
        $library = $this->library->getLibrary();
        $selectedPath = $library['default_path'];
        $document = $selectedPath ? $this->library->getDocument($selectedPath) : null;
        $selectedDocument = $document ? $this->library->renderDocument($document) : null;

        return view('studio.docs.index', [
            'library' => $library,
            'selectedDocument' => $selectedDocument,
        ]);
    }

    public function show(Request $request, string $path)
    {
        $library = $this->library->getLibrary();
        $document = $this->library->getDocument($path);

        abort_if(!$document, 404);

        return view('studio.docs.index', [
            'library' => $library,
            'selectedDocument' => $this->library->renderDocument($document),
        ]);
    }

    public function article(Request $request, string $path)
    {
        $document = $this->library->getDocument($path);

        abort_if(!$document, 404);

        return view('studio.docs.article', [
            'document' => $this->library->renderDocument($document),
        ]);
    }
}

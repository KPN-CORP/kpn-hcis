<?php

namespace App\Http\Controllers;

use App\Models\HRDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;  // Tambahkan ini
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Http\Request;

class DocumentGeneratorController extends Controller
{
    function GeneratorDoc()
    {
        $userId = Auth::id();
        $parentLink = 'Document Generator';
        $link = 'Document';

        $employeeId = auth()->user()->employee_id;

        $documents = HRDocument::where('employee_id', $employeeId)->get();
        // dd($documents);

        return view('hcis.document.generatorDocUpload', [
            'userId' => $userId,
            'documents' => $documents,
            'parentLink' => $parentLink,
            'link' => $link
        ]);
    }

    public function GeneratorUpload(Request $request)
    { 
        $userId = Auth::id();  
        $employeeId = auth()->user()->employee_id;

        $request->validate([
            'letter_name' => 'required|string',
            'template' => 'required|file|mimes:docx'
        ]);

        try {
            $templateFile = $request->file('template');
            $phpWord = IOFactory::load($templateFile->getPathname());

            $templateVariables = [];
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        preg_match_all('/\$\{([^}]+)\}/', $text, $matches);
                        if (!empty($matches[1])) {
                            foreach ($matches[1] as $variable) {
                                $templateVariables[] = $variable;
                            }
                        }
                    }
                }
            }

            $templateVariables = array_unique($templateVariables);

            // Buat direktori jika belum ada
            $templateDirectory = storage_path('templates/result/'.$employeeId);
            if (!file_exists($templateDirectory)) {
                mkdir($templateDirectory, 0777, true);
            }

            // Generate nama file yang unik
            $fileName = $request->letter_name . '_' . time() . '.docx';
            $templatePath = 'templates/result/'.$employeeId . '/' . $fileName;

            // Simpan file ke public disk
            $templateFile->storeAs('public/templates/result/'.$employeeId.'/', $fileName);

            $document = new HRDocument();
            $document->letter_name = $request->letter_name;
            $document->template_path = $templatePath;
            $document->variables = json_encode($templateVariables);
            $document->created_by = $userId;
            $document->employee_id = $employeeId;
            $document->save();

            return redirect()->back()->with('success', 'Document template uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }


    public function GeneratorEdit($id)
    {
        $userId = Auth::id();
        $parentLink = 'Document Generator';
        $link = 'Document';
        $employeeId = auth()->user()->employee_id;

        // Ambil data dokumen
        $document = HRDocument::findOrFail($id);
        
        // Validasi jika dokumen tidak ditemukan
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found');
        }
        
        // Validasi jika file template tidak ada
        if (!file_exists(storage_path('app/public/'.$document->template_path))) {
            return redirect()->back()->with('error', 'Template file not found');
        }
        // dd($document);
        
        try {
            // Gunakan file template yang sudah ada
            $template_path = $document->template_path;
            
            // Load template dan ambil variabel menggunakan TemplateProcessor
            try {
                $templateProcessor = new TemplateProcessor(storage_path('app/public/'.$template_path));
                $placeholders = $templateProcessor->getVariables();
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error reading template file: ' . $e->getMessage());
            }

            return view('hcis.document.generatorDocEdit', [
                'userId' => $userId,
                'parentLink' => $parentLink,
                'link' => $link,
                'letter_name' => $document->letter_name,
                'template_path' => Storage::url($template_path), // This will generate the correct public URL
                'document' => $document,
                'placeholders' => $placeholders,
                'employeeId' => $employeeId,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error editing document: ' . $e->getMessage());
        }
    }

    public function GeneratorPreview(Request $request)
    {
        $request->validate([
            'template_path' => 'required|string',
            'letter_name' => 'required|string',
        ]);

        $cleanPath = str_replace('/storage/', '', $request->input('template_path'));
        $templatePath = storage_path('app/public/' . $cleanPath);

        if (!is_file($templatePath)) {
            throw new \Exception("Template file not found or is not a valid file: $templatePath");
        }

        try {
            $templateProcessor = new TemplateProcessor($templatePath);

            // Ganti placeholder dengan nilai dari form
            foreach ($request->input('fields') as $placeholder => $value) {
                // $placeholder sudah mengandung spasi yang asli
                $templateProcessor->setValue($placeholder, $value);
            }

            // Generate nama file preview yang unik
            $previewFileName = uniqid() . '_preview.docx';
            $previewRelativePath = 'templates/preview/' . $previewFileName;
            $previewFullPath = storage_path('app/public/' . $previewRelativePath);

            // Pastikan direktori preview ada
            $previewDir = dirname($previewFullPath);
            if (!file_exists($previewDir)) {
                mkdir($previewDir, 0777, true);
            }

            // Simpan file preview
            $templateProcessor->saveAs($previewFullPath);

            $this->cleanupOldPreviews();

            return response()->json([
                'success' => true,
                'preview_path' => 'storage/' . $previewRelativePath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage()
            ], 500);
        }
    }

    private function cleanupOldPreviews()
    {
        $files = glob(storage_path('app/public/templates/preview/*'));
        foreach($files as $file) {
            if(is_file($file) && time() - filemtime($file) >= 3600) {
                unlink($file);
            }
        }
    }

    public function GeneratorDownload(Request $request)
    {
        $request->validate([
            'template_path' => 'required|string',
            'letter_name' => 'required|string',
            'fields' => 'required|array'
        ]);

        $cleanPath = str_replace('/storage/', '', $request->input('template_path'));
        $templatePath = storage_path('app/public/' . $cleanPath);

        if (!is_file($templatePath)) {
            throw new \Exception("Template file not found or is not a valid file: $templatePath");
        }

        try {
            $templateProcessor = new TemplateProcessor($templatePath);
            
            // Proses fields yang dikirim dari form
            foreach ($request->input('fields') as $placeholder => $value) {
                // $placeholder sudah mengandung spasi yang asli
                $templateProcessor->setValue($placeholder, $value);
            }

            $resultDir = storage_path('app/public/templates/result');
            if (!file_exists($resultDir)) {
                mkdir($resultDir, 0777, true);
            }

            $resultPath = $resultDir . '/' . $request->input('letter_name') . '_generated.docx';
            $templateProcessor->saveAs($resultPath);

            return response()->download($resultPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            throw new \Exception("Failed to process template file: " . $e->getMessage());
        }
    }

    public function GeneratorDelete($id)
    {
        try {
            // Cari dokumen berdasarkan ID
            $document = HRDocument::findOrFail($id);

            // Hapus file template jika ada
            if ($document->template_path && file_exists(storage_path('app/public/'. $document->template_path))) {
                unlink(storage_path('app/public/'.$document->template_path));
                
                // Hapus folder jika kosong
                $folderPath = dirname(storage_path('app/public/'.$document->template_path));
                if (is_dir($folderPath) && count(scandir($folderPath)) <= 2) { // 2 karena . dan ..
                    rmdir($folderPath);
                }
            }

            // Hapus record dari database
            $document->delete();

            return redirect()->route('docGenerator')
                ->with('success', 'Document template deleted successfully');

        } catch (\Exception $e) {
            return redirect()->route('docGenerator')
                ->with('error', 'Error deleting document: ' . $e->getMessage());
        }
    }

}

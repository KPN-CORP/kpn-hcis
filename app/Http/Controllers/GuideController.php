<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuideController extends Controller
{
    function index() {
        $parentLink = 'Guides';
        $link = 'User Guideline';

        $datas = Guide::all();
        $dataUser = Guide::where('category', 'user')->get();
        $dataAdmin = Guide::where('category', 'admin')->get();
        
        return view('pages.guides.app', compact('parentLink', 'link', 'datas', 'dataUser', 'dataAdmin'));

    }

    function store(Request $request) {
        $request->validate([
            'files' => 'required|mimes:pdf|max:10240',
        ]);

        $fileName = $request->fileName;
        $category = 'user';

        if ($request->input('category', false)) {
            $category = 'admin';
        }

        if ($request->file('files')) {
            $file = $request->file('files');
            $filename = time() . '_' . $fileName; // $file->getClientOriginalName()
            $path = $file->storeAs('uploads', $filename.'.pdf', 'public');
            $fileSize = $file->getSize();

            $model =  new Guide;
            $model->name = $fileName;
            $model->category = $category;
            $model->description = $request->description;
            $model->file_path = $path;
            $model->file_size = $fileSize;
            $model->created_by = Auth::user()->id;
            
            $model->save();

            return back()->with('success', 'File has been uploaded.')
                         ->with('files', $filename);
        }

        return back()->withErrors(['files' => 'Please select a file to upload.']);

    }

    public function destroy($id): RedirectResponse

    {
        $guide = Guide::find($id);

        if ($guide) {

            if (Storage::disk('public')->exists($guide->file_path)) {
                Storage::disk('public')->delete($guide->file_path);
            }

            $guide->updated_by = Auth::user()->id;
            $guide->save();

            $guide->delete();
    
            return redirect()->route('guides')->with('success', 'File deleted successfully!');
        }
        return redirect()->route('guides')->with('error', 'File not found.');

    }
}

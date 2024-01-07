<?php

namespace App\Http\Controllers;

use App\Models\letter_type;
use App\Models\letter;
use Illuminate\Http\Request;
use App\Models\User;
use Excel;
use App\Exports\LetterExport;

class LetterTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getClassificate()
    {
        // Ambil semua jenis surat
        $letterTypes = letter_type::orderBy('letter_code', 'ASC')->simplePaginate(5);
        $letters = Letter::get();

        $letterCounts = [];

        foreach ($letters as $letter) {
            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }

        return view('letter.classificate.index', compact('letterTypes', 'letterCounts'));
    }

    
    public function searchClassificate(Request $request)
    {
        $keyword = $request->input('name');
        $letterTypes = letter_type::where('name_type', 'like', "%$keyword%")->orderBy('name_type', 'ASC')->simplePaginate(5);
        $letters = Letter::get();

        $letterCounts = [];

        foreach ($letters as $letter) {
            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }

        return view('letter.classificate.index', compact('letterTypes', 'letterCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createClassificate()
    {
        return view('letter.classificate.create');
    }

    public function createLetters()
    {
        $user = User::where('role', 'guru')->orderBy('name', 'ASC');
        return view('letter.letters.createLetter.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'letter_code' => 'required|min:5',
            'name_type' => 'required',
        ]);
    
        letter_type::create([
            'letter_code' => $request->letter_code,
            'name_type' => $request->name_type
        ]);
        return redirect()->route('letter.classificate.data')->with('success', 'Berhasil Menambahkan Klasifikasi Surat Baru!');
    }

    public function downloadExcel()
    {
        $letterCounts = [];


        $file_name = 'Klasifikasi Surat.xlsx';

        $export = new LetterExport($letterCounts);

        return Excel::download($export, $file_name);
    }

    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $letterTypes = letter_type::find($id);
        $dataLetter = letter::where('letter_type_id', $id)->get();

        $letterCounts = [];

        foreach ($dataLetter as $letter) {
            $recipientId = json_decode($letter->recipients, true);

            $recipients = User::whereIn('id', $recipientId)->get();

            $letter->recipientsData = $recipients;

            if (!isset($letterCounts[$letter->letter_type_id])) {
                $letterCounts[$letter->letter_type_id] = 1;
            } else {
                $letterCounts[$letter->letter_type_id]++;
            }
        }

        return view('letter.classificate.detail', compact('letterTypes','dataLetter', 'letterCounts'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $letter_type = letter_type::find($id);
        $letter_code = $letter_type['letter_code'];
        return view('letter.classificate.edit', compact('letter_type', 'letter_code'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'letter_code' => 'required|min:5',
            'name_type' => 'required',
        ]);

        letter_type::where('id', $id)->update([
            'letter_code' => $request->letter_code,
            'name_type' => $request->name_type
        ]);

        return redirect()->route('letter.classificate.data')->with('success', 'Berhasil Mengubah Data Klasifikasi Surat!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // cari dan hapus data
        letter_type::where('id', $id)->delete();
        return redirect()->back()->with('delete', 'Berhasil Menghapus Data Surat Klasifikasi');
    }
}

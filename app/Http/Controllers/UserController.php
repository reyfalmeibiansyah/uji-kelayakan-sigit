<?php

namespace App\Http\Controllers;

use App\Models\letter_type;
use App\Models\letter;
use App\Models\result;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usersStaff = User::where('role', 'staff')->count();
        $usersGuru = User::where('role', 'guru')->count();
        $allClassificate = letter_type::count();
        $allLetters = letter::count();
        return view('dashboard', compact('usersGuru','usersStaff', 'allClassificate', 'allLetters'));
    }
    

    public function getDataGuru()
    {
        $users = User::where('role', 'guru')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.guru.index', compact('users'));
    }

    public function getDataStaff()
    {
        $users = User::where('role', 'staff')->orderBy('name', 'ASC')->simplePaginate(5);
        return view('user.staff.index', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function createGuru()
    {
        return view('user.guru.create');
    }

    public function createStaff()
    {
        return view('user.staff.create');
    }

    public function searchGuru(Request $request)
    {
        $keyword = $request->input('name');
        $users = User::where('name', 'like', "%$keyword%")->where('role', 'guru')->orderBy('name', 'ASC')->simplePaginate(5);

        return view('user.guru.index', compact('users'));
    }

    public function searchStaff(Request $request)
    {
        $keyword = $request->input('name');
        $users = User::where('name', 'like', "%$keyword%")->where('role', 'staff')->orderBy('name', 'ASC')->simplePaginate(5);

        return view('user.staff.index', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|min:5',
            'role' => 'required'
        ]);

        // Ambil tiga karakter pertama dari nama dan email
        $namaUser = substr($request->name, 0, 3);
        $emailUser = substr($request->email, 0, 3);

        // Gabungkan tiga karakter pertama dari nama dan email sebagai password default
        $defaultPassword = Hash::make($namaUser . $emailUser);

        // Buat pengguna baru dengan data yang valid
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $defaultPassword
        ]);
        return redirect()->back()->with('success', 'Berhasil Menambahkan Data Baru!');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit($id)
    {
        $user = User::find($id);

        if ($user->role == 'staff') {
            return view('user.staff.edit', compact('user'));
        }
        else {
            return view('user.guru.edit', compact('user'));
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|min:5',
            'role' => 'required',
            'password' => 'required'
        ]);

        $hashedPassword = Hash::make($request->password);

        User::where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $hashedPassword,
        ]);

        if ($request->role == 'staff') {
            return redirect()->route('user.staff.data')->with('success', 'Berhasil Mengubah Data Pengguna!');
        }
        else {
            return redirect()->route('user.guru.data')->with('success', 'Berhasil Mengubah Data Pengguna!');
        }

    }

    public function authLogin (request $request) {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        // simpan data dari inputan email dan password ke dalam variable untuk memudahkan pemanggilnya
        $user = $request->only(['email', 'password']);
        // attempt : mengecek kecocokan email dan password kemudian menyimpan nya ke dalam class Auth 
        // (Memberi identitas data riwayat login ke projectnya)
        if (Auth::attempt($user)) {
            // perbedaan redirect() dan redirect()->route ?
            return redirect('/dashboard'); 
            // memanggil lewat path /
        } else {
            return redirect()->back()->with('failed', 'Login gagal! silahkan coba lagi');
        } // memanggil lewat name
    }

    public function logout(){
        // menghapus atau menghilangkan data session login
        Auth::logout();
        return redirect()->route('login'); 
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // cari dan hapus data
        User::where('id', $id)->delete();
        return redirect()->back()->with('delete', 'Berhasil Menghapus Data Pengguna');
    }
}

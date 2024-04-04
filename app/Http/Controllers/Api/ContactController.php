<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        $contact = Contact::latest()->paginate(5);
        return new ContactResource(true, 'List Data Contact', $contact);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|max:100',
            'no_telp'     => 'required',
            'alamat'   => 'required|max:200',
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $contact = Contact::create([
            'nama'     => $request->nama,
            'no_telp'   => $request->no_telp,
            'alamat' => $request->alamat,
            'image'     => $image->hashName(),
        ]);

        return new ContactResource(true, 'Data Contact Berhasil Ditambahkan!', $contact);
    }

    public function show($id)
    {
        $contact = Contact::find($id);
        return new ContactResource(true, 'Detail Data Contact!', $contact);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|max:100',
            'no_telp'     => 'required',
            'alamat'   => 'nullable|max:200',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contact = Contact::find($id);

        if ($request->hasFile('image')) {

            //unggah image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //hapus old image
            Storage::delete('public/posts/' . basename($contact->image));

            //update contact dengan new image
            $contact->update([
                'nama'     => $request->nama,
                'no_telp'   => $request->no_telp,
                'alamat' => $request->alamat,
                'image'     => $image->hashName(),
            ]);
        } else {

            //update tanpa image
            $contact->update([
                'nama'     => $request->nama,
                'no_telp'   => $request->no_telp,
                'alamat' => $request->alamat,
            ]);
        }

        return new ContactResource(true, 'Data Contact Berhasil Diubah!', $contact);
    }
}

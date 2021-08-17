<?php

namespace App\Http\Controllers;

use App\Models\Data;
use DeepCopy\Exception\CloneException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
class DataController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return
     */
    public function store($id)
    {
        $request = request();
        $token = $request->bearerToken();

        $encryptionKey = $token;
        $valueToEncrypt = $request->post('value');
        if($encryptionKey && $id && $valueToEncrypt) {
            $toKey = $encryptionKey;
            $cipher = "AES-128-CBC";

            // validate encryption key
            if (!Encrypter::supported($toKey, $cipher)) {
                Log::warning('Invalid encryption key used ');
                return [];
            }

            $encrypterTo = new Encrypter($toKey, $cipher);
            $data = Data::find($id);


             if($data) {
                 // record already exists so update the value
                 try {
                     $encryptedToString = $encrypterTo->encryptString($valueToEncrypt);
                     $data->value = $encryptedToString;
                     $data->save();
                 } catch (EncryptException $e) {
                     Log::warning('Invalid encryption key used' . $e->getMessage());
                     return [];
                 }

             } else {
                 try {
                     // create new record
                     $data  = new Data();
                     $data->id = $id;
                     $data->value = $encrypterTo->encryptString($valueToEncrypt);
                     $data->save();
                 } catch (EncryptException $e) {
                     Log::warning('Invalid encryption key used' . $e->getMessage());
                     return [];
                 }
             }

        } else {
            Log::warning('Invalid request');
            return [];
        }
        return response()->json('Successfully updated', 204);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $request = request();
        $token = $request->bearerToken();


        if($id && $token) {
            $data = Data::find($id);
            if($data) {
                try {
                    $fromKey = $token;
                    $cipher = "AES-128-CBC";

                    // validate encryption key
                    if (!Encrypter::supported($fromKey, $cipher)) {
                        Log::warning('Invalid encryption key used ');
                        return [];
                    }
                    $encrypterFrom = new Encrypter($fromKey, $cipher);

                    $decryptedFromString = $encrypterFrom->decryptString($data->value);
                } catch (DecryptException $e) {
                    Log::warning('Invalid encryption key used ' . $e->getMessage());
                    return [];
                }


            } else {
                Log::warning('Record not found');
                return response()->json('Record not found', 404);
            }

        } else {
            Log::warning('Invalid request');
            return response()->json('Invalid request', 400);
        }

        return $decryptedFromString;


    }
}

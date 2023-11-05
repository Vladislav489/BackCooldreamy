<?php

namespace App\Traits;

use Image;

trait FileStoreTrait {
    public function store_file($file) {
        try {
            $links = new \stdClass();

            $filenamewithextension = $file->getClientOriginalName();

            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);

            //get file extension
            $extension = $file->getClientOriginalExtension();

            //filename to store
            $filenametostore = $filename . '_' . time() . '.' . $extension;

            //Upload File
            $file->storeAs('public/files', $filenametostore);
            $link = env('APP_URL') . "/storage/files/" . $filenametostore;
            $links = new \stdClass();
            $links->link = $link;
            return $links;
        } catch (\Exception $e) {
            return response()->json((['message' => $e->getMessage()]), 500);
        }
    }

}

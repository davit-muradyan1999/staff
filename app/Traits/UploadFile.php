<?php
namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Str;
use Log;

trait UploadFile
{
    public function uploadFile($uploadedFile, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(30);

        if (preg_match("/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).base64,.*/", $uploadedFile, $type)) {

            $uploadedFile = substr($uploadedFile, strpos($uploadedFile, ',') + 1);
            $type = strtolower(explode('/', $type[1])[1]); // jpg, png, gif

            if($type == 'vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                $type = 'xlsx';
            } else if($type == 'vnd.openxmlformats-officedocument.wordprocessingml.document') {
                $type = 'docx';
            } else if($type == 'vnd.ms-excel') {
                $type = 'xls';
            } else if($type == 'plain') {
                $type = 'csv';
            }

            // if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            //     throw new \Exception('invalid image type');
            // }

            $uploadedFile = str_replace(' ', '+', $uploadedFile);
            $uploadedFile = base64_decode($uploadedFile);

            if ($uploadedFile === false) {
                throw new \Exception('base64_decode failed');
            }

            $pathAndName = $folder . '/' . $name . '.' . $type;

            Storage::disk($disk)->put($pathAndName, $uploadedFile);
            return $pathAndName;
        } else {
            // Log::info($uploadedFile->getClientOriginalExtension());
            return $uploadedFile ? $uploadedFile->storeAs($folder, $name.'.'.$uploadedFile->getClientOriginalExtension(), $disk) : null;
        }

        return false;
    }

    private function base64_mimetype(string $encoded, bool $strict = true): ?string {
        // Log::info(base64_decode($encoded));
        if ($decoded = base64_decode($encoded)) {
            $tmpFile = tmpFile();
            $tmpFilename = stream_get_meta_data($tmpFile)['uri'];

            file_put_contents($tmpFilename, $decoded);

            return mime_content_type($tmpFilename) ?: null;
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\AttachmentServiceInterface;
use App\Services\Interfaces\FilesServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

readonly class DownloadController {
    public function __construct(
        private AttachmentServiceInterface $attachmentService,
        private FilesServiceInterface $filesService,
    ){}

    public function attachment($id){
        try {
            return $this->attachmentService->download($id);
        } catch (\Throwable) {
            return redirect()->back()->with(["error" => "can't download"]);
        }
    }

    public function file($id){
        try {
            return $this->filesService->download($id);
        } catch (\Throwable $e) {
            return redirect()->back()->with(["error" => "can't download."]);
        }
    }

    public function version($id){
        try {
            return $this->filesService->downloadVersion($id);
        } catch (\Throwable $e) {
            return redirect()->back()->with(["error" => "can't download."]);
        }
    }
}

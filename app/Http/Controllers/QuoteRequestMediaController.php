<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Models\QuoteRequestMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteRequestMediaController extends Controller
{
    /**
     * Streams an attached photo or video to the homeowner or a painter invited to quote.
     * Uses a file response so browsers can seek within videos (byte ranges).
     */
    public function show(Request $request, QuoteRequest $quoteRequest, QuoteRequestMedia $media): BinaryFileResponse
    {
        abort_unless($media->quote_request_id === $quoteRequest->id, 404);

        $user = $request->user();
        $isOwner = $quoteRequest->user_id === $user->id;
        $isInvitedPainter = $user->provider?->quoteRequests()->whereKey($quoteRequest->id)->exists() ?? false;
        abort_unless($isOwner || $isInvitedPainter, 403);

        $disk = Storage::disk(QuoteRequestMedia::DISK);
        abort_unless($disk->exists($media->path), 404);

        return response()->file($disk->path($media->path), [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => ($media->isVideo() || $media->isDisplayableImage() ? 'inline' : 'attachment').'; filename="'.addslashes($media->original_name).'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}

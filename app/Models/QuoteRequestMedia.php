<?php

namespace App\Models;

use Database\Factories\QuoteRequestMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quote_request_id', 'path', 'mime_type', 'size_bytes', 'original_name'])]
class QuoteRequestMedia extends Model
{
    /** @use HasFactory<QuoteRequestMediaFactory> */
    use HasFactory;

    public const DISK = 'local';

    public const MAX_FILES = 6;

    public const MAX_KILOBYTES = 51200;

    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'mp4', 'mov', 'webm'];

    /** @return BelongsTo<QuoteRequest, $this> */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Images browsers can show inline. iPhone HEIC photos are offered as a download instead.
     */
    public function isDisplayableImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true);
    }
}

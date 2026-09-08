<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'domain',
        'title',
        'provider',
        'model',
        'format',
        'length',
        'language',
        'original_word_count',
        'summary_word_count',
        'reading_time_saved_minutes',
        'summary_markdown',
    ];

    protected $casts = [
        'original_word_count' => 'integer',
        'summary_word_count' => 'integer',
        'reading_time_saved_minutes' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the HTML rendered from the summary markdown.
     */
    public function getSummaryHtmlAttribute(): string
    {
        return Str::markdown($this->summary_markdown ?? '');
    }

    /**
     * Get formatted reading time saved string.
     */
    public function getReadingTimeSavedFormattedAttribute(): string
    {
        if ($this->reading_time_saved_minutes <= 0) {
            return '< 1 min';
        }

        if ($this->reading_time_saved_minutes < 1) {
            return '~30 sec';
        }

        return round($this->reading_time_saved_minutes, 1).' mins';
    }

    /**
     * Percentage reduction in words.
     */
    public function getReductionPercentageAttribute(): int
    {
        if ($this->original_word_count <= 0) {
            return 0;
        }

        $reduction = (($this->original_word_count - $this->summary_word_count) / $this->original_word_count) * 100;

        return (int) max(0, min(99, round($reduction)));
    }
}

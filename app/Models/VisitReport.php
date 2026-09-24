<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class VisitReport extends Model
{
    protected $table = 'visit_report';

    protected $fillable = [
        'id',
        'employee_id',
        'business_customer_id',
        'customer_pic_name',
        'activity_type_id',
        'r_level_id',
        'activity_category_id',
        'estimated_value',
        'activity_description',
        'action_plan',
        'voc',
        'visit_type',
        'communication_channel',
        'activity_topic',
        'document_file_url',
        'visit_date',
        'visit_time',
        'validation_status',
        'validator_id',
        'validation_notes',
        'validated_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'validated_at' => 'datetime',
        'estimated_value' => 'decimal:2',
        'document_file_url' => 'array',
    ];

    protected static function booted(): void
    {
        // Berkas/lampiran/foto disimpan permanen sebagai arsip dan tidak dihapus dari disk:
        /*
        static::deleting(function (VisitReport $report) {
            // 1. Hapus berkas fisik lampiran dokumen
            if (!empty($report->document_file_url) && is_array($report->document_file_url)) {
                foreach ($report->document_file_url as $item) {
                    if (!empty($item['file'])) {
                        Storage::disk('public')->delete($item['file']);
                    }
                }
            }

            // 2. Hapus berkas fisik foto kunjungan
            foreach ($report->photos as $photo) {
                if (!empty($photo->file_url)) {
                    Storage::disk('public')->delete($photo->file_url);
                }
            }
        });
        */
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validator_id');
    }

    public function businessCustomer(): BelongsTo
    {
        return $this->belongsTo(BusinessCustomer::class, 'business_customer_id');
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function rLevel(): BelongsTo
    {
        return $this->belongsTo(RLevel::class);
    }

    public function activityCategory(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'visit_report_service');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ReportLocation::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function getDocumentUrlAttribute(): ?string
    {
        $urls = $this->document_urls;
        return $urls[0]['url'] ?? null;
    }

    /**
     * @return array<int, array{name: string, caption: ?string, path: string, url: string, is_image: bool, is_pdf: bool}>
     */
    public function getDocumentUrlsAttribute(): array
    {
        if (empty($this->document_file_url)) {
            return [];
        }

        $items = is_array($this->document_file_url)
            ? $this->document_file_url
            : [$this->document_file_url];

        $results = [];
        foreach ($items as $item) {
            $filePath = is_array($item) ? ($item['file'] ?? null) : $item;
            $caption = is_array($item) ? ($item['caption'] ?? null) : null;

            if (empty($filePath) || !is_string($filePath)) {
                continue;
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $results[] = [
                'name' => basename($filePath),
                'caption' => $caption,
                'path' => $filePath,
                'url' => Storage::disk('public')->url($filePath),
                'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'webp']),
                'is_pdf' => $ext === 'pdf',
            ];
        }

        return $results;
    }
}

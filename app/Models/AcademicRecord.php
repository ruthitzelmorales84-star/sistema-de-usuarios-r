<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicRecord extends Model
{
    protected $fillable = [
        'user_id',
        'student_name',
        'student_email',
        'absences',
        'grade',
        'equivalence',
    ];

    protected $casts = [
        'absences' => 'integer',
        'grade' => 'float',
    ];

    public static function calculateEquivalence(float $grade): string
    {
        if ($grade < 7.0) {
            return 'NA';
        }

        if ($grade < 8.0) {
            return 'SA';
        }

        if ($grade < 9.0) {
            return 'DE';
        }

        return 'AU';
    }

    public static function letterGradeLabel(?string $letterGrade): string
    {
        $code = strtoupper(trim((string) $letterGrade));

        return match ($code) {
            'AU' => 'AU',
            'DE' => 'DE',
            'SA' => 'SA',
            'NA' => 'NA',
            default => $code !== '' ? $code : 'NA',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (AcademicRecord $record) {
            $record->equivalence = self::calculateEquivalence((float) $record->grade);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
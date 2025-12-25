<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Is Honeymelon free?',
                'answer' => 'Honeymelon is free to download. A paid license is required to use the app. The license is a one-time purchase with no subscriptions.',
                'order' => 1,
            ],
            [
                'question' => 'What are the system requirements?',
                'answer' => 'Honeymelon requires macOS 13 (Ventura) or later and an Apple Silicon chip (M1 or newer). Intel-based Macs are not supported.',
                'order' => 2,
            ],
            [
                'question' => 'Does Honeymelon work offline?',
                'answer' => 'Yes. Honeymelon requires a one-time internet connection to activate your license. After that, the app runs fully offline—no telemetry, no license checks, nothing.',
                'order' => 3,
            ],
            [
                'question' => 'What file formats are supported?',
                'answer' => 'Honeymelon supports MP4, MOV, MKV, WebM, and GIF for video; M4A, MP3, FLAC, WAV, and Opus for audio; and PNG, JPEG, and WebP for images. Powered by FFmpeg.',
                'order' => 4,
            ],
            [
                'question' => 'Do you collect my files or data?',
                'answer' => 'No. All conversions happen locally on your Mac. Your files never leave your device, and we collect zero telemetry or usage data.',
                'order' => 5,
            ],
            [
                'question' => 'Can I use my license on multiple Macs?',
                'answer' => "Each license activates on one Mac device. The activation is one-time and cannot be transferred. If you need Honeymelon on multiple Macs, you'll need a separate license for each.",
                'order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}

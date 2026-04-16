<?php

namespace JeexWebp\Conversion;

defined( 'ABSPATH' ) || exit;

class ConversionResult {

    public const STATUS_SUCCESS = 'success';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED  = 'failed';

    private string $status;
    private string $sourcePath;
    private string $outputPath;
    private int $originalSize;
    private int $convertedSize;
    private string $message;

    public function __construct(
        string $status,
        string $sourcePath = '',
        string $outputPath = '',
        int $originalSize = 0,
        int $convertedSize = 0,
        string $message = ''
    ) {
        $this->status        = $status;
        $this->sourcePath    = $sourcePath;
        $this->outputPath    = $outputPath;
        $this->originalSize  = $originalSize;
        $this->convertedSize = $convertedSize;
        $this->message       = $message;
    }

    public static function success( string $source, string $output, int $origSize, int $newSize ): self {
        return new self( self::STATUS_SUCCESS, $source, $output, $origSize, $newSize );
    }

    public static function skipped( string $source, string $reason ): self {
        return new self( self::STATUS_SKIPPED, $source, '', 0, 0, $reason );
    }

    public static function failed( string $source, string $reason ): self {
        return new self( self::STATUS_FAILED, $source, '', 0, 0, $reason );
    }

    public function isSuccess(): bool {
        return self::STATUS_SUCCESS === $this->status;
    }

    public function isSkipped(): bool {
        return self::STATUS_SKIPPED === $this->status;
    }

    public function isFailed(): bool {
        return self::STATUS_FAILED === $this->status;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getSourcePath(): string {
        return $this->sourcePath;
    }

    public function getOutputPath(): string {
        return $this->outputPath;
    }

    public function getOriginalSize(): int {
        return $this->originalSize;
    }

    public function getConvertedSize(): int {
        return $this->convertedSize;
    }

    public function getSavedBytes(): int {
        if ( ! $this->isSuccess() ) {
            return 0;
        }
        return max( 0, $this->originalSize - $this->convertedSize );
    }

    public function getSavingsPercent(): float {
        if ( $this->originalSize <= 0 ) {
            return 0.0;
        }
        return round( ( $this->getSavedBytes() / $this->originalSize ) * 100, 1 );
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function toArray(): array {
        return [
            'status'         => $this->status,
            'source'         => $this->sourcePath,
            'output'         => $this->outputPath,
            'original_size'  => $this->originalSize,
            'converted_size' => $this->convertedSize,
            'saved_bytes'    => $this->getSavedBytes(),
            'savings_pct'    => $this->getSavingsPercent(),
            'message'        => $this->message,
        ];
    }
}

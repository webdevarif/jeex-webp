<?php

namespace JeexWebp\Conversion\Format;

defined( 'ABSPATH' ) || exit;

interface FormatInterface {

    public function getName(): string;

    public function getExtension(): string;

    public function getMimeType(): string;

    public function isAvailable(): bool;
}

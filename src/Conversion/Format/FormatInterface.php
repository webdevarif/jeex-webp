<?php

namespace JeexWebp\Conversion\Format;

interface FormatInterface {

    public function getName(): string;

    public function getExtension(): string;

    public function getMimeType(): string;

    public function isAvailable(): bool;
}

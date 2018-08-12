<?php

namespace Cerberus;

class debug {
    public function disable($line) {
        return '//'.$line;
    }
}
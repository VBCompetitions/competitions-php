<?php

namespace VBCompetitions\Competitions;

interface BreakInterface {
    public function getDate() : ?string;
    public function getDuration() : ?string;
    public function getName() : ?string;
    public function getStart() : ?string;
}

<?php

namespace Ari\PdfHelper;

class LinesBreakPointResult
{

  public $text;
  public $position;
  public $counts;
  public $char = null;
  public $textEnd = false;

  public function getDebugText()
  {
    return implode(' | ', array(
      implode(', ', $this->counts->getRemainFormat(1)),
      $this->position->length,
      empty($this->char) ? 'x' : $this->char->diff.' ('.$this->char->charFound.')',
    ) );
  }

}
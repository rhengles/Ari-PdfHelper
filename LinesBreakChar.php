<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreakCharResult;

class LinesBreakChar
{

	protected $breakChars = array(' ', '-');
	protected $hasSpace = true;

	public function spaceInChars( $chars )
	{
		foreach( $chars as $c ) {
			if ( $c === ' ' ) return true;
		}
		return false;
	}

	public function setBreakChars( $chars )
	{
		$this->breakChars = $chars;
		$this->hasSpace = $this->spaceInChars( $chars );
	}

	protected function makeResult( $text, $char, $pos, $posFound, $textEnd = false )
	{
		$result = new LinesBreakCharResult();
		$result->text = $text;
		$result->charFound = $char;
		$result->length = $posFound;
		$result->diff = $pos - $posFound;
		$result->textEnd = $textEnd;
		return $result;
	}

	public function findNearest( $text, $charset, $length )
	{
		$nearest = null;
		foreach ( $this->breakChars as $c ) {
			$pos = mb_strrpos( $text, $c, 0, $charset );
			if ( $pos === false ) continue;
			if ( empty( $nearest ) || $pos > $nearest['pos'] ) {
				$nearest = array(
					'char' => $c,
					'pos' => $pos + ( $c === ' ' ? 0 : 1 ),
				);
				if ( $length - $pos === 1 ) return $nearest;
			}
		}
		return $nearest;
	}

	public function breakLine( $text, $charset, $pos )
	{
		if ( mb_strlen($text, $charset) <= $pos ) {
			return $this->makeResult( $text, null, $pos, mb_strlen($text, $charset), true );
		}
		if ( $this->hasSpace && mb_substr($text, $pos, 1, $charset) === ' ' ) {
			return $this->makeResult( mb_substr($text, 0, $pos, $charset), ' ', $pos, $pos );
		}
		$text = mb_substr( $text, 0, $pos, $charset );
		$nearest = $this->findNearest( $text, $charset, $pos );
		if ( empty( $nearest ) ) {
			return $this->makeResult( $text, null, $pos, $pos );
		} else {
			$text = mb_substr( $text, 0, $nearest['pos'], $charset );
			return $this->makeResult( $text, $nearest['char'], $pos, $nearest['pos'] );
		}
	}

}
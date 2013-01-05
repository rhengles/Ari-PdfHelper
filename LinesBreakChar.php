<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreakCharResult;

class LinesBreakChar
{

	protected $breakChars = array(' ', '-');
	protected $hasSpace = true;

	protected $strFn;

	public function setStringFn( String\IStringFn $strFn )
	{
		$this->strFn = $strFn;
		return $this;
	}

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

	public function findNearest( $text, $length )
	{
		$nearest = null;
		foreach ( $this->breakChars as $c ) {
			$pos = $this->strFn->rpos( $text, $c );
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

	public function breakLine( $text, $pos )
	{
		if ( $this->strFn->length( $text ) <= $pos ) {
			return $this->makeResult( $text, null, $pos, $this->strFn->length( $text ), true );
		}
		if ( $this->hasSpace && $this->strFn->substr( $text, $pos, 1 ) === ' ' ) {
			return $this->makeResult( $this->strFn->substr( $text, 0, $pos ), ' ', $pos, $pos );
		}
		$text = $this->strFn->substr( $text, 0, $pos );
		$nearest = $this->findNearest( $text, $pos );
		if ( empty( $nearest ) ) {
			return $this->makeResult( $text, null, $pos, $pos );
		} else {
			$text = $this->strFn->substr( $text, 0, $nearest['pos'] );
			return $this->makeResult( $text, $nearest['char'], $pos, $nearest['pos'] );
		}
	}

}
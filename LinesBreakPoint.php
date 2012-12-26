<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\MeasureText;
use Ari\PdfHelper\MeasureTextCounts;
use Ari\PdfHelper\LinesBreakPointResult;

class LinesBreakPoint
{

	protected $font;
	protected $size;
	protected $width;
	protected $average; // Characters per line
	protected $breakChars;
	protected $measure;

	protected $precise = false;
	protected $debug = false;

	public function __construct( $font = null, $size = null )
	{
		if ( isset( $font ) ) $this->setFont( $font );
		if ( isset( $size ) ) $this->setSize( $size );
	}

	public function getMeasure()
	{
		if ( empty( $this->measure ) ) {
			$ms = new MeasureText();
			$ms->setFont( $this->font )
				->setSize( $this->size );
			$this->measure = $ms;
		}
		return $this->measure;
	}

	public function setFont( $font )
	{
		$this->font = $font;
		return $this;
	}

	public function setSize( $size )
	{
		$this->size = $size;
		return $this;
	}

	public function setWidth( $width )
	{
		$this->width = $width;
		return $this;
	}

	public function setAverage( $average )
	{
		$this->average = $average;
		return $this;
	}

	public function getAverage()
	{
		return $this->average;
	}

	public function getAverageOrGuess()
	{
		if ( empty( $this->average ) ) {
			$this->average = intval( $this->width / $this->getMeasure()->guessCharWidth() );
		}
		return $this->average;
	}

	public function setBreakChars( $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setDebug( $debug )
	{
		$this->debug = $debug;
		return $this;
	}

	public function breakLineAtChars( $text, $cut )
	{
		if ( empty( $this->breakChars ) ) return null;
		return $this->breakChars->breakLine( $text, $cut );
	}

	public function makeResult( $text, $nearest, $char, $counts )
	{
		$result = new LinesBreakPointResult();
		$result->text = empty( $char ) ? $text : $char->text;
		$result->position = $nearest;
		$result->counts = $counts;
		$result->char = $char;
		$result->textEnd = empty( $char )
			? $nearest->length === strlen( $text )
			: $char->textEnd;
		return $result;
	}

	public function findBreak( $text )
	{
		$estimate = $this->getAverageOrGuess();
        $len = strlen( $text );
        if ( $estimate > $len ) $estimate = $len;
		$counts = new MeasureTextCounts();
		$counts
			->setWidth( $this->width )
			->setText( $text )
			->setMeasure( $this->getMeasure() );
			//->measure( 0 );
		$nearest = null;
        do {
            //echo 'estimate ', $estimate;
			$estimate = $counts->measure( $estimate );
            if ( $estimate->remain >= 0 && $estimate->length == $len ) {
                // acabou a string! Vamos embora.
				$nearest = $estimate;
				break;
            }
            if ( $estimate->remain >= -($this->size/4)
              && ( empty( $nearest )
                || $estimate->remain < $nearest->remain ) ) {
                $nearest = $estimate;
				if ( $estimate->remain < ($this->size) ) break; // limitador para melhorar a performance
            }
            $last = $estimate;
            $estimate = intval( $last->length / $last->width * $this->width );
			if ( $estimate > $len ) $estimate = $len;
			// o argumento "precise" deixa o código mais lento, use apenas se for necessário
			if ( $this->precise ) {
				$est = $counts->get( $estimate );
				if ( !empty( $est ) ) {
					$estimate += ( $estimate > 0 && $est->remain < 0 ? -1 : 1 );
				}
            }
        } while ( !$counts->has($estimate) && ( empty($nearest) || $counts->count() < 4 ) ); // limitador para melhorar a performance
		$char = $this->breakLineAtChars( $text, $nearest->length );
		return $this->makeResult( $text, $nearest, $char, $counts );
	}

}
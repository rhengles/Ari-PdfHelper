<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\MeasureText;
use Ari\PdfHelper\MeasureTextCounts;
use Ari\PdfHelper\LinesBreakPointResult;

class LinesBreakPoint
{

	protected $width;

	protected $measure;
	protected $strFn;
	protected $breakChars;

	protected $average; // Characters per line

	protected $precise = false;
	protected $debug = false;

	public function setMeasure( MeasureText $ms )
	{
		$this->measure = $ms;
		return $this;
	}

	public function setStringFn( String\IStringFn $strFn )
	{
		$this->strFn = $strFn;
		return $this;
	}

	public function setBreakChars( LinesBreakChar $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setWidth( $width )
	{
		$this->width = $width;
		return $this;
	}

	public function setDebug( $debug )
	{
		$this->debug = $debug;
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
			$this->average = intval( $this->width / $this->measure->guessCharWidth() );
		}
		return $this->average;
	}

	public function breakLineAtChars( $text, $cut )
	{
		if ( empty( $this->breakChars ) ) return null;

		return $this->breakChars->breakLine( $text, $cut );
	}

	public function makeResult( $text, $nearest, $char, $counts )
	{
		$result = new LinesBreakPointResult();
		$result->text = empty( $char )
			? $this->strFn->substr( $text, 0, $nearest->length )
			: $char->text;
		$result->position = $nearest;
		$result->counts = $counts;
		$result->char = $char;
		$result->textEnd = empty( $char )
			? $nearest->length === $this->strFn->length( $text )
			: $char->textEnd;
		return $result;
	}

	public function findBreak( $text )
	{
		$size = $this->measure->getSize();
		$estimate = $this->getAverageOrGuess();
		$len = $this->strFn->length( $text );
		if ( $estimate > $len ) $estimate = $len;
		$counts = new MeasureTextCounts();
		$counts
			->setMeasure( $this->measure )
			->setStringFn( $this->strFn )
			->setText( $text )
			->setWidth( $this->width );
		$nearest = null;
		do {
			$estimate = $counts->measure( $estimate );
			if ( $estimate->remain >= 0 && $estimate->length == $len ) {
				// acabou a string! Vamos embora.
				$nearest = $estimate;
				break;
			}
			if ( $estimate->remain >= -($size/4)
				&& ( empty( $nearest )
					|| $estimate->remain < $nearest->remain ) ) {
				$nearest = $estimate;
				if ( $estimate->remain < ($size/2) ) break; // limitador para melhorar a performance
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
		if ( $len > $nearest->length
			&& $nearest->remain > $size
			&& $counts->count() < 4 ) {
			$msg = "<pre>\r\n";
			$msg .= $text;
			$msg .= " (";
			$msg .= $len;
			$msg .= " = ";
			$msg .= $this->strFn->length( $text );
			$msg .= " = ";
			$msg .= $nearest->length;
			$msg .= " / ";
			$msg .= $nearest->remain;
			$msg .= " / ";
			$msg .= $counts->count();
			$msg .= ")\r\n";
			//$msg .= $estimate;
			//print_r( $counts );
			$msg .= "\r\n</pre>\r\n";
			throw new \Exception( $msg );
		}
		$char = $this->breakLineAtChars( $text, $nearest->length );
		//$char = null;
		return $this->makeResult( $text, $nearest, $char, $counts );
	}

}
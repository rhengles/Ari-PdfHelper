<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\MeasureTextResult;

class MeasureTextCounts
{

	protected $text;
	protected $width;

	protected $measure;
	protected $strFn;

	protected $counts = array();

	public function __construct( $width = null )
	{
		if ( isset( $width ) ) $this->setWidth( $width );
	}

	public function setText( $text )
	{
		$this->text = $text;
		return $this;
	}

	public function setWidth( $width )
	{
		$this->width = $width;
		return $this;
	}

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

	public function makeResult( $length, $width, $remain )
	{
		$result = new MeasureTextResult();
		$result->length = $length;
		$result->width  = $width;
		$result->remain = $remain;
		return $result;
	}

	public function measureText( $text )
	{
		return $this->measure->width( $text );
	}

	public function measure( $length )
	{
		$textWidth = $this->measureText( $this->strFn->substr( $this->text, 0, $length ) );
		$result = $this->makeResult( $length, $textWidth, $this->width - $textWidth );
		$this->counts[ $length ] = $result;
		return $result;
	}

	public function get( $length )
	{
		if ( $this->has( $length ) ) {
			return $this->counts[ $length ];
		}
		return null;
	}

	public function has( $length )
	{
		return isset( $this->counts[ $length ] );
	}

	public function count()
	{
		return count( $this->counts );
	}

	public function getRemainFormat( $dec )
	{
		$list = array();
		foreach ( $this->counts as $c ) {
			$list[] = number_format( $c->remain, $dec );
		}
		return $list;
	}

}
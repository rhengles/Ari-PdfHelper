Ari / PdfHelper
===============

Utilities for working with [ZendPdf](//github.com/zendframework/ZendPdf):

- Text Measurement
- Text wrapping into lines at closest length or specified characters


Sample Usage
------------

	<?php

	use ZendPdf\PdfDocument;
	use ZendPdf\Page;
	use ZendPdf\Font;
	use ZendPdf\Color\GrayScale;
	use Ari\PdfHelper\Paragraph;

	$pdf = new PdfDocument();

	$page = new Page(Page::SIZE_A4);
	$font = Font::fontWithName(Font::FONT_HELVETICA);
	$gray = new GrayScale( 0.8 );

	$para = new Paragraph( $page, $font, 8 /* font size */, 2 /* line spacing */ );
	$para->setDebug( true );
	$para->setDebugLineColor( $gray );

	$para->render( $reallyLongText,
		20  /* x position */,
		780 /* y position */,
		240 /* paragraph width */ );

	$pdf->pages[] = $page;

	header('Content-Type: application/pdf');
	echo $pdf->render();
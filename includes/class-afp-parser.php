<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AFP_Parser {

	public function parse_file( $path, $extension ) {
		$content = '';

		switch ( $extension ) {
			case 'pdf':
				$content = $this->extract_text_from_pdf( $path );
				break;
			case 'docx':
				$content = $this->extract_text_from_docx( $path );
				break;
			case 'md':
			case 'txt':
				$content = file_get_contents( $path );
				break;
		}

		return $this->parse_resume_content( $content );
	}

	private function extract_text_from_pdf( $path ) {
		try {
			$parser = new \Smalot\PdfParser\Parser();
			$pdf    = $parser->parseFile( $path );
			return $pdf->getText();
		} catch ( \Exception $e ) {
			return '';
		}
	}

	private function extract_text_from_docx( $path ) {
		try {
			$phpWord = \PhpOffice\PhpWord\IOFactory::load( $path );
			$text = '';
			foreach ( $phpWord->getSections() as $section ) {
				foreach ( $section->getElements() as $element ) {
					if ( method_exists( $element, 'getText' ) ) {
						$text .= $element->getText() . "\n";
					}
				}
			}
			return $text;
		} catch ( \Exception $e ) {
			return '';
		}
	}

	public function parse_resume_content( $content ) {
		$data = array(
			'full_name'  => '',
			'email'      => '',
			'phone'      => '',
			'linkedin'   => '',
			'github'     => '',
			'website'    => '',
			'dob'        => '',
			'skills'     => '',
			'summary'    => '',
			'experience' => '',
			'education'  => '',
		);

		// Full Name: Usually the first line or in CAPS
		if ( preg_match( '/^#\s+(.+)$/m', $content, $matches ) ) {
			$data['full_name'] = sanitize_text_field( trim( $matches[1] ) );
		} elseif ( preg_match( '/^([A-Z\s]{5,30})[\s—\-]/m', $content, $matches ) ) {
			$data['full_name'] = sanitize_text_field( trim( $matches[1] ) );
		} elseif ( preg_match( '/^([A-Z]{2,}\s+[A-Z]{2,}.*)$/m', $content, $matches ) ) {
			$data['full_name'] = sanitize_text_field( trim( $matches[1] ) );
		}

		// Email
		if ( preg_match( '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $content, $matches ) ) {
			$data['email'] = sanitize_email( $matches[0] );
		}

		// Phone
		if ( preg_match( '/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4,}/', $content, $matches ) ) {
			$data['phone'] = sanitize_text_field( $matches[0] );
		}

		// LinkedIn
		if ( preg_match( '/(linkedin\.com\/(?:in\/)?[a-z0-9-]+)/i', $content, $matches ) ) {
			$data['linkedin'] = 'https://' . $matches[1];
		}

		// GitHub
		if ( preg_match( '/(github\.com\/[a-z0-9-]+)/i', $content, $matches ) ) {
			$data['github'] = 'https://' . $matches[1];
		}

		// Website
		// Use a more specific regex to avoid matching emails or common social domains
		if ( preg_match( '/(?<![a-z0-9._%+-])(?:https?:\/\/)?(?:www\.)?([a-z0-9-]+\.(?:com|net|org|io|dev|me|info|biz|co|us))(?!\/(?:in\/|)|@)/i', $content, $matches ) ) {
			$domain = strtolower( $matches[1] );
			$social_domains = array( 'linkedin.com', 'github.com', 'facebook.com', 'twitter.com', 'instagram.com', 'gmail.com' );
			if ( ! in_array( $domain, $social_domains ) ) {
				$data['website'] = 'https://' . $matches[1];
			}
		}

		// Date of Birth
		$data['dob'] = $this->extract_by_anchor( $content, 'Date of Birth' );
		if ( empty( $data['dob'] ) ) $data['dob'] = $this->extract_by_anchor( $content, 'DOB' );

		// Skills
		if ( preg_match( '/TECHNICAL\s+SKILLS\s*\n+(.+?)(?=\n\n|\n[A-Z\s]{5,})/is', $content, $matches ) ) {
			$data['skills'] = sanitize_textarea_field( trim( $matches[1] ) );
		}

		// Professional Summary
		if ( preg_match( '/(?:PROFESSIONAL|EXECUTIVE)\s+SUMMARY\s*\n+(.+?)(?=\n\n|\n[A-Z\s]{5,}|$)/is', $content, $matches ) ) {
			$data['summary'] = sanitize_textarea_field( trim( $matches[1] ) );
		}

		// Experience
		if ( preg_match( '/(?:PROFESSIONAL\s+)?EXPERIENCE\s*\n+(.+?)(?=\n\n|\nEDUCATION|\nSKILLS|\nPROJECTS|$)/is', $content, $matches ) ) {
			$data['experience'] = sanitize_textarea_field( trim( $matches[1] ) );
		}

		// Education
		if ( preg_match( '/EDUCATION\s*\n+(.+?)(?=\n\n|\nEXPERIENCE|\nSKILLS|\nPROJECTS|$)/is', $content, $matches ) ) {
			$data['education'] = sanitize_textarea_field( trim( $matches[1] ) );
		}

		return $data;
	}

	private function extract_by_anchor( $content, $anchor ) {
		$pattern = '/(?:\*\*|)' . preg_quote( $anchor, '/' ) . '(?:\*\*|)\s*[:\-–—]\s*(.+)$/m';
		if ( preg_match( $pattern, $content, $matches ) ) {
			return sanitize_text_field( trim( $matches[1] ) );
		}
		return '';
	}
}

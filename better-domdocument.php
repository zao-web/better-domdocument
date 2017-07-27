<?php

class Better_DOMDocument extends DOMDocument {

	/**
	 * Initiate the DOMDocument object, ensuring UTF-8
	 *
	 * @see http://stackoverflow.com/a/8218649/1883421
	 *
	 * @since 3.0.0
	 *
	 * @param string $content HTML content
	 */
	public function __construct( $content ) {
		@$this->loadHTML( '<?xml encoding="UTF-8">' . $content );

		// Fixes data attributes like:
		// `data-gcatts="{&quot;align&quot;:&quot;right&quot;,&quot;linkto&quot;:&quot;attachment-page&quot;,&quot;size&quot;:&quot;full&quot;}"`
		// to correct:
		// data-gcatts='{"align":"right","linkto":"attachment-page","size":"full"}'
		$this->normalizeDocument();
	}

	/**
	 * Returns the normalized content.
	 *
	 * @since  3.0.0
	 *
	 * @return string  HTML content
	 */
	public function get_content() {
		$html = $this->saveHTML( $this );

		return str_replace( array(
			'<?xml encoding="UTF-8">',
		), '', $html );
	}

	public function removeInlineScripts() {
		foreach ( $this->getElementsByTagName( 'script' ) as $script ) {
			if ( ! $script->getAttribute( 'src' ) ) {
				$script->parentNode->removeChild( $script );
			}
		}

		return $this;
	}

	function removeElementById( $id ) {
		$el = $this->getElementById( $id );
		if ( $el ) {
			$el->parentNode->removeChild( $el );
		}

		return $this;
	}

	function removeSubElementsByClass( $domEl, $tagName, $className, $offset = null ) {
		$els = $this->getSubElementsByClass( $domEl, $tagName, $className, $offset );

		if ( $els instanceof DOMElement ) {
			$els->parentNode->removeChild( $els );
		} elseif ( $els && is_array( $els ) ) {
			foreach ( $els as $el ) {
				$el->parentNode->removeChild( $el );
			}
		}

		return $this;
	}

	function removeElementsByClass( $tagName, $className, $offset = null ) {
		$els = $this->getElementByClass( $tagName, $className, $offset );
		foreach ( $els as $el ) {
			$el->parentNode->removeChild( $el );
		}

		return $this;
	}

	function getElementByClass( $tagName, $classNames, $offset = null ) {
		return $this->getSubElementsByClass( $this, $tagName, $classNames, $offset );
	}

	function getSubElementsByClass( $domEl, $tagName, $classNames, $offset = null ) {
		$response = array();

		$childNodeList = $domEl->getElementsByTagName( $tagName );

		foreach ( (array) $classNames as $className ) {
			$tagCount = 0;
			for ( $i = 0; $i < $childNodeList->length; $i++ ) {
				$temp = $childNodeList->item( $i );

				if ( false !== stripos( $temp->getAttribute('class'), $className ) ) {
					if ( null !== $offset && $tagCount === $offset ) {
						$response = $temp;
						break;
					} else {
						$response[] = $temp;
					}

					$tagCount++;
				}
			}
		}

		return $response;
	}
}

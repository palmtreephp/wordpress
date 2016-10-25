<?php

namespace Palmtree\WordPress;

class AbstractCollection implements \ArrayAccess {
	protected $items = [];

	public function addItem( $key, $item ) {
		$this->items[ $key ] = $item;
	}

	public function addItems( array $items ) {
		foreach ( $items as $key => $item ) {
			$this->addItem( $key, $item );
		}
	}

	public function removeItem( $key ) {
		unset( $this->items[ $key ] );
	}

	public function getItems() {
		return $this->items;
	}

	public function getItem( $key ) {
		if ( ! isset( $this->items[ $key ] ) ) {
			throw new \InvalidArgumentException( "The item '$key' does not exist." );
		}

		$class = $this->items[ $key ];

		if ( is_string( $class ) && class_exists( $class ) ) {
			$this->items[ $key ] = new $class();
		}

		return $this->items[ $key ];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return $this->getItem( $offset ) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->getItem( $offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->addItem( $offset, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		$this->removeItem( $offset );
	}
}

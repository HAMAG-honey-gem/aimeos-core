<?php

/**
 * @copyright Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015
 * @package MShop
 * @subpackage Tag
 */


namespace Aimeos\MShop\Tag\Item;


/**
 * Default tag item implementation.
 *
 * @package MShop
 * @subpackage Tag
 */
class Standard
	extends \Aimeos\MShop\Common\Item\Base
	implements \Aimeos\MShop\Tag\Item\Iface
{
	private $values;

	/**
	 * Initializes the tag item object with the given values
	 */
	public function __construct( array $values = array( ) )
	{
		parent::__construct( 'tag.', $values );

		$this->values = $values;
	}


	/**
	 * Returns the language ID of the product tag item.
	 *
	 * @return string|null Language ID of the product tag item
	 */
	public function getLanguageId()
	{
		return ( isset( $this->values['langid'] ) ? (string) $this->values['langid'] : null );
	}


	/**
	 *  Sets the language ID of the product tag item.
	 *
	 * @param string|null $id Language ID of the product tag item
	 */
	public function setLanguageId( $id )
	{
		if( $id === $this->getLanguageId() ) { return; }

		$this->checkLanguageId( $id );
		$this->values['langid'] = $id;
		$this->setModified();
	}


	/**
	 * Returns the type id of the product tag item
	 *
	 * @return integer|null Type of the product tag item
	 */
	public function getTypeId()
	{
		return ( isset( $this->values['typeid'] ) ? (int) $this->values['typeid'] : null );
	}


	/**
	 * Sets the new type of the product tag item
	 *
	 * @param integer|null $id Type of the product tag item
	 */
	public function setTypeId( $id )
	{
		$id = (int) $id;
		if( $id === $this->getTypeId() ) { return; }

		$this->values['typeid'] = (int) $id;
		$this->setModified();
	}


	/**
	 * Returns the label of the product tag item.
	 *
	 * @return string Label of the product tag item
	 */
	public function getLabel()
	{
		return ( isset( $this->values['label'] ) ? (string) $this->values['label'] : '' );
	}


	/**
	 * Sets the Label of the product tag item.
	 *
	 * @param string $label Label of the product tag item
	 */
	public function setLabel( $label )
	{
		if( $label == $this->getLabel() ) { return; }

		$this->values['label'] = (string) $label;
		$this->setModified();
	}


	/**
	 * Returns the type code of the product tag item.
	 *
	 * @return string Type code of the product tag item
	 */
	public function getType()
	{
		return ( isset( $this->values['type'] ) ? (string) $this->values['type'] : null );
	}


	/**
	 * Returns the item type
	 *
	 * @return Item type, subtypes are separated by slashes
	 */
	public function getResourceType()
	{
		return 'tag';
	}


	/**
	 * Sets the item values from the given array.
	 *
	 * @param array $list Associative list of item keys and their values
	 * @return array Associative list of keys and their values that are unknown
	 */
	public function fromArray( array $list )
	{
		$unknown = array();
		$list = parent::fromArray( $list );

		foreach( $list as $key => $value )
		{
			switch( $key )
			{
				case 'tag.typeid': $this->setTypeId( $value ); break;
				case 'tag.languageid': $this->setLanguageId( $value ); break;
				case 'tag.label': $this->setLabel( $value ); break;
				default: $unknown[$key] = $value;
			}
		}

		return $unknown;
	}


	/**
	 * Returns the item values as array.
	 *
	 * @return Associative list of item properties and their values
	 */
	public function toArray()
	{
		$list = parent::toArray();

		$list['tag.typeid'] = $this->getTypeId();
		$list['tag.languageid'] = $this->getLanguageId();
		$list['tag.label'] = $this->getLabel();
		$list['tag.type'] = $this->getType();

		return $list;
	}

}
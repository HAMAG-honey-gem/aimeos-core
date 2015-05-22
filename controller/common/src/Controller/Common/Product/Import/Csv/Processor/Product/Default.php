<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015
 * @package Controller
 * @subpackage Common
 */


/**
 * Product processor for CSV imports
 *
 * @package Controller
 * @subpackage Common
 */
class Controller_Common_Product_Import_Csv_Processor_Product_Default
	extends Controller_Common_Product_Import_Csv_Processor_Abstract
	implements Controller_Common_Product_Import_Csv_Processor_Interface
{
	private $_cache;
	private $_listTypes;


	/**
	 * Initializes the object
	 *
	 * @param MShop_Context_Item_Interface $context Context object
	 * @param array $mapping Associative list of field position in CSV as key and domain item key as value
	 * @param Controller_Common_Product_Import_Csv_Processor_Interface $object Decorated processor
	 */
	public function __construct( MShop_Context_Item_Interface $context, array $mapping,
		Controller_Common_Product_Import_Csv_Processor_Interface $object = null )
	{
		parent::__construct( $context, $mapping, $object );

		/** controller/common/product/import/csv/processor/product/listtypes
		 * Names of the product list types that are updated or removed
		 *
		 * Aimeos offers associated items like "bought together" products that
		 * are automatically generated by other job controllers. These relations
		 * shouldn't normally be overwritten or deleted by default during the
		 * import and this confiuration option enables you to specify the list
		 * types that should be updated or removed if not available in the import
		 * file.
		 *
		 * Contrary, if you don't generate any relations automatically in the
		 * shop and want to import those relations too, you can set the option
		 * to null to update all associated items.
		 *
		 * @param array|null List of product list type names or null for all
		 * @since 2015.05
		 * @category Developer
		 * @category User
		 * @see controller/common/product/import/csv/domains
		 * @see controller/common/product/import/csv/processor/attribute/listtypes
		 * @see controller/common/product/import/csv/processor/catalog/listtypes
		 * @see controller/common/product/import/csv/processor/media/listtypes
		 * @see controller/common/product/import/csv/processor/price/listtypes
		 * @see controller/common/product/import/csv/processor/text/listtypes
		 */
		$default = array( 'default', 'suggestion' );
		$key = 'controller/common/product/import/csv/processor/product/listtypes';
		$this->_listTypes = $context->getConfig()->get( $key, $default );

		$this->_cache = $this->_getCache( 'product' );
	}


	/**
	 * Saves the product related data to the storage
	 *
	 * @param MShop_Product_Item_Interface $product Product item with associated items
	 * @param array $data List of CSV fields with position as key and data as value
	 * @return array List of data which hasn't been imported
	 */
	public function process( MShop_Product_Item_Interface $product, array $data )
	{
		$context = $this->_getContext();
		$listManager = MShop_Factory::createManager( $context, 'product/list' );
		$manager = MShop_Factory::createManager( $context, 'product' );

		$this->_cache->set( $product );

		$manager->begin();

		try
		{
			$pos = 0;
			$delete = array();
			$map = $this->_getMappedChunk( $data );
			$listItems = $product->getListItems( 'product', $this->_listTypes );

			foreach( $listItems as $listId => $listItem )
			{
				$refItem = $listItem->getRefItem();

				if( isset( $map[$pos] ) && ( !isset( $map[$pos]['product.code'] )
					|| ( $refItem !== null && $map[$pos]['product.code'] === $refItem->getCode() ) )
				) {
					$pos++;
					continue;
				}

				$listItems[$listId] = null;
				$delete[] = $listId;
				$pos++;
			}

			$listManager->deleteItems( $delete );

			foreach( $map as $pos => $list )
			{
				if( !isset( $map[$pos]['product.code'] ) || $list['product.code'] === '' || isset( $list['product.list.type'] )
					&& $this->_listTypes !== null && !in_array( $list['product.list.type'], (array) $this->_listTypes )
				) {
					continue;
				}

				if( ( $prodid = $this->_cache->get( $list['product.code'] ) ) === null )
				{
					$msg = 'No product for code "%1$s" available when importing product with code "%2$s"';
					throw new Controller_Jobs_Exception( sprintf( $msg, $list['product.code'], $product->getCode() ) );
				}

				if( ( $listItem = array_shift( $listItems ) ) === null ) {
					$listItem = $listManager->createItem();
				}

				$typecode = ( isset( $list['product.list.type'] ) ? $list['product.list.type'] : 'default' );
				$list['product.list.typeid'] = $this->_getTypeId( 'product/list/type', 'product', $typecode );
				$list['product.list.parentid'] = $product->getId();
				$list['product.list.refid'] = $prodid;
				$list['product.list.domain'] = 'product';

				$listItem->fromArray( $this->_addListItemDefaults( $list, $pos ) );
				$listManager->saveItem( $listItem );
			}

			$remaining = $this->_getObject()->process( $product, $data );

			$manager->commit();
		}
		catch( Exception $e )
		{
			$manager->rollback();
			throw $e;
		}

		return $remaining;
	}
}
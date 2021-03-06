<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2011
 * @copyright Aimeos (aimeos.org), 2015-2016
 */


namespace Aimeos\MShop\Locale\Manager\Currency;


/**
 * Test class for \Aimeos\MShop\Locale\Manager\Currency\Standard.
 */
class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp()
	{
		$this->object = new \Aimeos\MShop\Locale\Manager\Currency\Standard( \TestHelperMShop::getContext() );
	}


	protected function tearDown()
	{
		$this->object = null;
	}


	public function testCreateItem()
	{
		$this->assertInstanceOf( '\\Aimeos\\MShop\\Locale\\Item\\Currency\\Iface', $this->object->createItem() );
	}


	public function testSaveUpdateDeleteItem()
	{
		// insert case
		$item = $this->object->createItem();
		$item->setLabel( 'new name' );
		$item->setStatus( 1 );
		$item->setCode( 'XXX' );

		$this->object->saveItem( $item );
		$itemSaved = $this->object->getItem( $item->getId() );

		// update case
		$itemExp = clone $itemSaved;
		$itemExp->setLabel( 'new new name' );
		$this->object->saveItem( $itemExp );
		$itemUpd = $this->object->getItem( $itemExp->getId() );

		$this->object->deleteItem( $item->getId() );

		$context = \TestHelperMShop::getContext();

		$this->assertTrue( $item->getId() !== null );
		$this->assertEquals( $item->getId(), $itemSaved->getId() );
		$this->assertEquals( $item->getLabel(), $itemSaved->getLabel() );
		$this->assertEquals( $item->getStatus(), $itemSaved->getStatus() );
		$this->assertEquals( $item->getCode(), $itemSaved->getCode() );

		$this->assertEquals( $context->getEditor(), $itemSaved->getEditor() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeModified() );

		$this->assertEquals( $itemExp->getId(), $itemUpd->getId() );
		$this->assertEquals( $itemExp->getStatus(), $itemUpd->getStatus() );
		$this->assertEquals( $itemExp->getCode(), $itemUpd->getCode() );
		$this->assertEquals( $itemExp->getLabel(), $itemUpd->getLabel() );

		$this->assertEquals( $context->getEditor(), $itemUpd->getEditor() );
		$this->assertEquals( $itemExp->getTimeCreated(), $itemUpd->getTimeCreated() );
		$this->assertRegExp( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemUpd->getTimeModified() );


		$this->setExpectedException( '\\Aimeos\\MShop\\Exception' );
		$this->object->getItem( $item->getId() );
	}


	public function testGetItem()
	{
		$actual = $this->object->getItem( 'EUR' );

		$this->assertEquals( 'EUR', $actual->getId() );
		$this->assertEquals( 'Euro', $actual->getLabel() );
		$this->assertEquals( 1, $actual->getStatus() );
		$this->assertEquals( 'EUR', $actual->getCode() );
	}


	public function testSearchItems()
	{
		$search = $this->object->createSearch();

		$expr = [];
		$expr[] = $search->compare( '==', 'locale.currency.id', 'EUR' );
		$expr[] = $search->compare( '==', 'locale.currency.label', 'Euro' );
		$expr[] = $search->compare( '==', 'locale.currency.code', 'EUR' );
		$expr[] = $search->compare( '==', 'locale.currency.status', 1 );
		$expr[] = $search->compare( '>=', 'locale.currency.mtime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '>=', 'locale.currency.ctime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '>=', 'locale.currency.editor', '' );

		$total = 0;
		$search->setConditions( $search->combine( '&&', $expr ) );
		$results = $this->object->searchItems( $search, [], $total );

		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 1, $total );

		// search without base criteria, slice & total
		$search = $this->object->createSearch();
		$search->setConditions( $search->compare( '~=', 'locale.currency.label', 'CFA' ) );
		$search->setSlice( 0, 1 );
		$results = $this->object->searchItems( $search, [], $total );
		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 2, $total );

		foreach( $results as $itemId => $item ) {
			$this->assertEquals( $itemId, $item->getId() );
		}
	}


	public function testGetResourceType()
	{
		$result = $this->object->getResourceType();

		$this->assertContains( 'locale/currency', $result );
	}


	public function testGetSearchAttributes()
	{
		foreach( $this->object->getSearchAttributes() as $attribute ) {
			$this->assertInstanceOf( '\\Aimeos\\MW\\Criteria\\Attribute\\Iface', $attribute );
		}
	}


	public function testGetSubManager()
	{
		$this->setExpectedException( '\\Aimeos\\MShop\\Exception' );
		$this->object->getSubManager( 'unknown' );
	}
}

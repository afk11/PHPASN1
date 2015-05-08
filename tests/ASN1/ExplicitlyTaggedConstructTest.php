<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FG\Test\ASN1;

use FG\ASN1\Identifier;
use FG\Test\ASN1TestCase;
use FG\ASN1\ExplicitlyTaggedObject;
use FG\ASN1\Universal\PrintableString;

class ExplicitlyTaggedObjectTest extends ASN1TestCase
{
    public function testGetType()
    {
        $asn = new ExplicitlyTaggedObject(0x1E, new PrintableString('test'));
        $expectedType = Identifier::create(Identifier::CLASS_CONTEXT_SPECIFIC, $isConstructed = true, 0x1E);
        $this->assertEquals($expectedType, $asn->getType());
    }

    public function testGetIdentifier()
    {
        $asn = new ExplicitlyTaggedObject(0x1E, new PrintableString('test'));
        $expectedIdentifier = chr(Identifier::create(Identifier::CLASS_CONTEXT_SPECIFIC, $isConstructed = true, 0x1E));
        $this->assertEquals($expectedIdentifier, $asn->getIdentifier());
    }

    public function testGetTag()
    {
        $object = new ExplicitlyTaggedObject(0, new PrintableString('test'));
        $this->assertEquals(0, $object->getTag());

        $object = new ExplicitlyTaggedObject(1, new PrintableString('test'));
        $this->assertEquals(1, $object->getTag());
    }

    public function testGetLength()
    {
        $string = new PrintableString('test');
        $object = new ExplicitlyTaggedObject(0, $string);
        $this->assertEquals($string->getObjectLength() + 2, $object->getObjectLength());
    }

    public function testGetContent()
    {
        $string = new PrintableString('test');
        $object = new ExplicitlyTaggedObject(0, $string);
        $this->assertEquals($string, $object->getContent());
    }

    public function testGetBinary()
    {
        $tag = 0x01;
        $string = new PrintableString('test');
        $expectedType = chr(Identifier::create(Identifier::CLASS_CONTEXT_SPECIFIC, $isConstructed = true, $tag));
        $expectedLength = chr($string->getObjectLength());

        $encodedStringObject = $string->getBinary();
        $object = new ExplicitlyTaggedObject($tag, $string);
        $this->assertBinaryEquals($expectedType.$expectedLength.$encodedStringObject, $object->getBinary());
    }

    /**
     * @dataProvider getTags
     * @depends testGetBinary
     */
    public function testFromBinary($originalTag)
    {
        $originalStringObject = new PrintableString('test');
        $originalObject = new ExplicitlyTaggedObject($originalTag, $originalStringObject);
        $binaryData = $originalObject->getBinary();

        $parsedObject = ExplicitlyTaggedObject::fromBinary($binaryData);
        $this->assertEquals($originalObject, $parsedObject);
    }

    public function getTags()
    {
        return array(
            array(0x02),
            array(0x00004002),
        );
    }

    /**
     * This does not work since PHP shifts in 1 bits from the left
     */
    public function testPhpShiftOperatorWithLeadingOneBit()
    {
        $value = 3735928559;
        // binary   = 11011110 10101101 10111110 11101111
        // expected = 01101111 01010110 11011111 01110111
        // actual   = 11101111 01010110 11011111 01110111
        //$this->assertEquals(decbin(1867964279), decbin($value >> 1));

        $this->assertEquals(1867964279, $value >> 1);
        $this->assertEquals( 933982139, $value >> 2);
        $this->assertEquals( 466991069, $value >> 3);
        $this->assertEquals( 233495534, $value >> 4);
        $this->assertEquals( 116747767, $value >> 5);
        $this->assertEquals(  58373883, $value >> 6);
        $this->assertEquals(  29186941, $value >> 7);
    }

    /**
     * This does also work
     */
    public function testPhpShiftByDivisionWithLeadingOneBit()
    {
        $value = 3735928559;
        $this->assertEquals(1867964279, intval($value / 2));
        $this->assertEquals( 933982139, intval($value / 4));
        $this->assertEquals( 466991069, intval($value / 8));
        $this->assertEquals( 233495534, intval($value / 16));
        $this->assertEquals( 116747767, intval($value / 32));
        $this->assertEquals(  58373883, intval($value / 64));
        $this->assertEquals(  29186941, intval($value / 128));
    }

    /**
     * The following works
     */
    public function testPhpShiftOperatorWithLeadingZeroBit()
    {
        $value = 1588444911;
        // binary   = 01011110 10101101 10111110 11101111
        // expected = 00101111 01010110 11011111 01110111
        $this->assertEquals(decbin(794222455), decbin($value >> 1));

        $this->assertEquals(794222455, $value >> 1);
    }

    /**
     * @depends testGetBinary
     * @depends testPhpShiftOperatorWithLeadingOneBit
     */
    public function testFromBinaryWithHugeTagNumber()
    {
        $originalStringObject = new PrintableString('test');
        $originalObject = new ExplicitlyTaggedObject(0xDEADBEEF, $originalStringObject);
        $binaryData = $originalObject->getBinary();

        $parsedObject = ExplicitlyTaggedObject::fromBinary($binaryData);
        $this->assertEquals($originalObject, $parsedObject);
    }
}


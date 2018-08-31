<?php
namespace Mentax\PJRPC\Hydrator;


use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DateTimeDenormalizer extends ObjectNormalizer
{

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param string $class The expected class to instantiate
     * @param string $format Format the given data was extracted from
     * @param array $context Options available to the denormalizer
     *
     * @return object
     *
     * @throws BadMethodCallException   Occurs when the normalizer is not called in an expected context
     * @throws InvalidArgumentException Occurs when the arguments are not coherent or not supported
     * @throws UnexpectedValueException Occurs when the item cannot be hydrated with the given data
     * @throws ExtraAttributesException Occurs when the item doesn't have attribute to receive given data
     * @throws LogicException           Occurs when the normalizer is not supposed to denormalize
     * @throws RuntimeException         Occurs if the class cannot be instantiated
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {

        $accessor = \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor();

        foreach ($data as $k => $v) {
            if (is_string($v) && preg_match('#[1-2][0-9]{3}\-[0-1][0-9]\-[0-3][0-9]T[0-2][0-9]\:[0-5][0-9]\:[0-5][0-9][\+|\-][0-1][0-9]\:[0-5][0-9]#si', $v)) {
                $accessor->setValue($data, sprintf('[%s]', $k), new \DateTime($v));
            }
        }

        $result = parent::denormalize($data, $class, $format, $context);
        return $result;
    }
}

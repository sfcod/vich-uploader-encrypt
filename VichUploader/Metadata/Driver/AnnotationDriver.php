<?php

namespace SfCod\VichUploaderEncrypt\VichUploader\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Metadata\Driver\AdvancedDriverInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use SfCod\VichUploaderEncrypt\VichUploader\Mapping\UploadableField;
use Metadata\ClassMetadata as JSMClassMetadata;
use Vich\UploaderBundle\Metadata\ClassMetadata;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class AnnotationDriver implements AdvancedDriverInterface
{
    /**
     * @deprecated
     */
    const UPLOADABLE_ANNOTATION = Uploadable::class;

    /**
     * @deprecated
     */
    const UPLOADABLE_FIELD_ANNOTATION = UploadableField::class;

    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class): ?JSMClassMetadata
    {
        if (!$this->isUploadable($class)) {
            return null;
        }

        $classMetadata = new ClassMetadata($class->name);
        $classMetadata->fileResources[] = $class->getFileName();

        foreach ($class->getProperties() as $property) {
            $uploadableField = $this->reader->getPropertyAnnotation($property, UploadableField::class);
            if (null === $uploadableField) {
                continue;
            }
            /* @var $uploadableField UploadableField */
            $fieldMetadata = [
                'mapping' => $uploadableField->getMapping(),
                'propertyName' => $property->getName(),
                'fileNameProperty' => $uploadableField->getFileNameProperty(),
                'size' => $uploadableField->getSize(),
                'mimeType' => $uploadableField->getMimeType(),
                'originalName' => $uploadableField->getOriginalName(),
                'dimensions' => $uploadableField->getDimensions(),
                'encrypted' => $uploadableField->getEncrypted(),
            ];

            //TODO: store UploadableField object instead of array
            $classMetadata->fields[$property->getName()] = $fieldMetadata;
        }

        return $classMetadata;
    }

    public function getAllClassNames(): array
    {
        return [];
    }

    protected function isUploadable(\ReflectionClass $class)
    {
        return null !== $this->reader->getClassAnnotation($class, Uploadable::class);
    }
}

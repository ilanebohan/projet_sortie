<?php

namespace App\Tests\Form\Type;

use App\Form\VilleType;
use App\Entity\Ville;
use App\Form\Type\TestedType;
use App\Model\TestObject;
use Symfony\Component\Form\Test\TypeTestCase;

class VilleTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'nom' => 'villeTest',
            'codePostal' => '95000',
        ];

        $model = new Ville();
        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(VilleType::class, $model);

        $expected = new Ville();
        $expected->setNom('villeTest');
        $expected->setCodePostal('95000');
        // ...populate $expected properties with the data stored in $formData

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }
}

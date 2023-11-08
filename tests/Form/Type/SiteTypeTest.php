<?php

namespace App\Tests\Form\Type;

use App\Entity\Site;
use App\Form\SiteType;
use Symfony\Component\Form\Test\TypeTestCase;

class SiteTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'nom' => 'siteTest'
        ];

        $model = new Site();
        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(SiteType::class, $model);

        $expected = new Site();
        $expected->setNom('siteTest');
        // ...populate $expected properties with the data stored in $formData

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected when the form was submitted
        $this->assertEquals($expected, $model);
    }
}

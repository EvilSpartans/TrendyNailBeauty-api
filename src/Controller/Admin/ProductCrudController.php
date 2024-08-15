<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des articles')
            ->setPageTitle('detail', 'Article')
            ->setPageTitle('new', 'Ajouter un article')
            ->setPageTitle('edit', "Modifier l'article")
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un article');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('imageFile', 'Image')->setFormType(VichImageType::class)->hideOnIndex()->hideOnDetail(),
            ImageField::new('image', 'Image')->setBasePath('/uploads/images/')->hideOnForm(),
            TextField::new('name', 'Titre'),
            AssociationField::new('category', 'Catégorie'),
            TextField::new('slug', 'Slug')->onlyOnDetail(),
            TextEditorField::new('description', 'Description')->hideOnIndex(),
            IntegerField::new('price', 'Prix'),
            DateTimeField::new('createdAt', 'Date')->hideOnIndex()->hideOnForm(),
            BooleanField::new('onSale', 'En solde'),
            ChoiceField::new('stock', 'En stock')->setChoices([
                'Oui' => 'Oui',
                'Non' => 'Non',
            ])->hideOnIndex(),
        ];
    }
}

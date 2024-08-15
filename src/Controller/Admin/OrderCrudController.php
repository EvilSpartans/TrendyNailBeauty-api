<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des commandes')
            ->setPageTitle('edit', "Mettre à jour la commande")
            ->setHelp('edit', 'Modifier le status pour avertir le client')
            ->setPaginatorPageSize(10)
            ->setEntityPermission('ROLE_ADMIN', 'ROLE_MODO');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('user', 'Client')->setFormTypeOption('disabled', true),
            AssociationField::new('products', 'Articles')->hideOnForm()->hideOnIndex(),
            NumberField::new('price', 'Total')->setFormTypeOption('disabled', true),
            ChoiceField::new('status', 'Statut')->setChoices([
                'En attente' => 'Pending',
                'Expédiée' => 'Shipped',
                'Réceptionnée' => 'Received',
                'Remboursée' => 'Refunded',
                'Annulée' => 'Cancelled',
            ]),
            DateTimeField::new('createdAt', 'Date')->hideOnForm(),
            TextField::new('phone', 'Téléphone')->hideOnIndex()->hideOnForm(),
            TextField::new('street', 'Adresse')->hideOnIndex()->hideOnForm(),
            TextField::new('zipCode', 'Code postal')->hideOnIndex()->hideOnForm(),
            TextField::new('city', 'Ville')->hideOnIndex()->hideOnForm(),
            TextField::new('country', 'Pays')->hideOnIndex()->hideOnForm(),
            TextEditorField::new('customerNotes', 'Notes laissées')->setTemplatePath('admin/textField.html.twig')->hideOnIndex()->hideOnForm(),
            TextField::new('shipping', 'Livraison')->hideOnIndex()->hideOnForm(),
            UrlField::new('invoice', 'Facture')->setTemplatePath('admin/urlField.html.twig')->hideOnForm()
        ];
    }
}

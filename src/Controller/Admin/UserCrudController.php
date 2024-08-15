<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('edit', "Modifier l'utilisateur")
            ->setHelp('edit', 'Modifier le rôle pour accorder des privilèges')
            ->setPaginatorPageSize(10);
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
            TextField::new('username', 'Pseudo')->setFormTypeOption('disabled', true),
            EmailField::new('email', 'Email')->setFormTypeOption('disabled', true),
            TextField::new('phone', 'Téléphone')->hideOnForm(),
            TextField::new('gender', 'Genre')->hideOnIndex()->hideOnForm(),
            TextField::new('street', 'Adresse')->hideOnIndex()->hideOnForm(),
            TextField::new('zipCode', 'Code postal')->hideOnIndex()->hideOnForm(),
            TextField::new('city', 'Ville')->hideOnIndex()->hideOnForm(),
            TextField::new('country', 'Pays')->hideOnIndex()->hideOnForm(),
            IntegerField::new('ordersCount', 'Nombre de commandes')->hideOnIndex()->hideOnForm(),
            ChoiceField::new('roles')->setChoices([
                'ROLE_USER' => 'ROLE_USER',
                'ROLE_MODO' => 'ROLE_MODO',
                'ROLE_ADMIN' => 'ROLE_ADMIN',
            ])->allowMultipleChoices()->hideOnIndex(),
        ];
    }
}

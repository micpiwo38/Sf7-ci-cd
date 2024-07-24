<?php

namespace App\Controller\Admin;

use App\Entity\Produits;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class ProduitsCrudController extends AbstractCrudController
{
    private $security;
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Produits::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nom du produit'),
            TextareaField::new('description', 'Description du produit')->renderAsHtml(),
            MoneyField::new('price', 'Prix du produit')->setCurrency('EUR'),
            ImageField::new('image', 'Image du produit')
                ->setBasePath('/produits/img')
                ->setUploadDir('/public/produits/img'),
            BooleanField::new('stock', 'Produit en stock'),
            AssociationField::new('categorie', 'Catégorie du produit')->autocomplete(),
            AssociationField::new('user', 'Nom du vendeur')->setFormTypeOptions([
                'query_builder' => function () use ($user)
                {
                    return $this->em->getRepository(User::class)->createQueryBuilder('u')
                        ->where('u.email = :email')
                        ->setParameter('email', $user->getUserIdentifier());
                }
            ])

        ];
    }

    //Recuperer le vendeur
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->security->getUser();
        // Filter les produits par utilisateur connecté
        if($this->isGranted('ROLE_USER'))
        {
            $qb->andWhere('entity.user = :user')
                ->setParameter('user', $user->getId());
        }
        return $qb;
    }
}

<?php
declare(strict_types=1);

namespace Console;


class ComponentsAttributes
{
    public static function header(string $entity, string $layout, bool $repo): string
    {
        $repoUse = $repo  ? sprintf("use App\Repository\%sRepository;\n", $entity) : '';
        $repoString = $repo ? sprintf("#[ORM\Entity(repositoryClass: %sRepository::class)]\n", $entity) : '';

        return <<<EOPHP
<?php
declare(strict_types=1);

namespace App\Entity;

{$repoUse}use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '$entity')]
{$repoString}class $entity
{
    #[ORM\Column(name: 'rec_id', type: 'integer')]
    private int \$rec_id;

EOPHP;

    }

    public static function Text($field, bool $id = false): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, $id);
        $idString = $id ? "#[ORM\Id]\n    ": null;

        return <<<EOPHP

    $idString#[ORM\Column(name: '$col', type: 'string', length: 255)]
    private string \${$param};
    
EOPHP;
    }

    public static function Number($field, bool $id = false): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, $id);
        $idString = $id ? "#[ORM\Id]\n    ": null;

        return <<<EOPHP

    $idString#[ORM\Column(name: '$col', type: 'integer')]
    private int \${$param};
    
EOPHP;
    }

    public static function Date($field): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, false);

        return <<<EOPHP

    #[ORM\Column(name: '$col', type: 'fmdate')]
    private DateTime \${$param};
    
EOPHP;
    }

    public static function Time($field): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, false);

        return <<<EOPHP

    #[ORM\Column(name: '$col', type: 'fmtime')]
    private DateTime \${$param};
    
EOPHP;
    }

    public static function Timestamp($field): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, false);

        return <<<EOPHP

    #[ORM\Column(name: '$col', type: 'fmdatetime')]
    private DateTime \${$param};
    
EOPHP;
    }

    public static function Container($field): string
    {
        $col = self::getColName($field);
        $param = self::getParamName($field, false);

        return <<<EOPHP

    #[ORM\Column(name: '$col', type: 'string', length: 255)]
    private string \${$param};
    
EOPHP;
    }

    public static function footer(): string
    {
        return <<<EOPHP

}
EOPHP;

    }

    private static function getColName($field): string
    {
        return strpos($field, '::') !== false ? "'{$field}'" : $field;
    }

    private static function getParamName($field, bool $id): string
    {
        if($id) {
            return 'id';
        }
        return lcfirst(
            str_replace(['::', '.', '_', ' '], '', $field)
        );
    }

    public static function repo($entity): string
    {
        return <<<EOPHP
<?php
declare(strict_types=1);
 
namespace App\Repository;

use App\Entity\\{$entity};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * {$entity}Repository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class {$entity}Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry \$registry)
    {
        parent::__construct(\$registry, {$entity}::class);
    }
}
EOPHP;
    }

}

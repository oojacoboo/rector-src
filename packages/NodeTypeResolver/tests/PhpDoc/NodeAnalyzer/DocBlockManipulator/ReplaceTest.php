<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer\DocBlockManipulator;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ReplaceTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
        $this->docBlockManipulator = self::$container->get(DocBlockManipulator::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $originalFile, Type $oldType, Type $newType, string $expectedFile): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromFile($originalFile);

        $node = new Nop();
        $node->setDocComment(new Doc(Filesystem::read($originalFile)));

        $this->docBlockManipulator->renamePhpDocType($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);

        $newPhpDocContent = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        $this->assertStringEqualsFile($expectedFile, $newPhpDocContent);
    }

    public function provideData(): Iterator
    {
        $oldObjectType = new ObjectType('PHP_Filter');
        $newObjectType = new ObjectType('PHP\Filter');

        yield [
            __DIR__ . '/ReplaceSource/before.txt',
            $oldObjectType,
            $newObjectType,
            __DIR__ . '/ReplaceSource/after.txt',
        ];
        yield [
            __DIR__ . '/ReplaceSource/before2.txt',
            $oldObjectType,
            $newObjectType,
            __DIR__ . '/ReplaceSource/after2.txt',
        ];
    }

    private function createPhpDocInfoFromFile(string $originalFile): PhpDocInfo
    {
        $docContent = FileSystem::read($originalFile);
        $node = new Nop();
        $node->setDocComment(new Doc($docContent));

        return $this->phpDocInfoFactory->createFromNode($node);
    }
}

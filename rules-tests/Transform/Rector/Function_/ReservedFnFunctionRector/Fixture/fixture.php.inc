<?php

namespace Rector\Tests\Transform\Rector\Function_\ReservedFnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        function reservedFn($value)
        {
            return $value;
        }

        reservedFn(5);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Function_\ReservedFnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        function f($value)
        {
            return $value;
        }

        f(5);
    }
}

?>

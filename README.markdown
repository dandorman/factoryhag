# FactoryHag

This is a cheesy knockoff of the magnificent
[factory\_girl](https://github.com/thoughtbot/factory_girl).

Some things I like about factories:

  - Testing becomes faster, especially so the more tests that need to leverage
    the same model.
  - Tests are more readable, because only the pertinent attributes of a model
    need to be set in any given test.

Anyway, check out the real deal if you need more information on factories in
general.

## Requirements

  - **PHP 5.3**. I wanted to leverage the newer features of PHP, specifically
    namespaces.
  - **Zend Framework**. It's what I'm familiar with, though I've grown
    increasingly dissatisfied with Zend_Db_Table as an ORM. The code is written
    so that it wouldn't be very difficult to abstract out this requirement, I
    just haven't had any real need to.

## Usage

Drop it in your app somewhere. I use `library`, but folks are probably using
`vendor` now? Whatever floats your boat.

Right now, I `require_once` a file called `tests/factories.php`. It looks a
little something like this:

    // util provides a bunch of quick shortcuts to FactoryHag methods.
    require_once 'FactoryHag/util.php';

    use FactoryHag as Hag; // 'cause I'm too lazy to type FactoryHag

    // then define your factories:

    Hag\define('user', array(
      'username' => 'test',
      'password' => 'secret',
    ));

So then a test file might look like this:

    use FactoryHag as Hag;

    class UserTest extends PHPUnit_Framework_TestCase
    {
      public function tearDown()
      {
        Hag\flush();
      }

      public function testUsernameEqualsWhateverItIsSetTo()
      {
        $user = Hag\f('user');
        $this->assertEquals('test', $user->username);
      }
    }

That's a pretty crap test, but hopefully it gets the idea across. A couple
things to note:

  - **`Hag\flush`**. In the `tearDown` method, I'm calling `FactoryHag\flush`,
    which wipes out any records created by FactoryHag, hopefully getting the
    database back to a somewhat more pristine state.
  - **`Hag\f`**. This is the method that creates a new record and saves it in
    the database. It's short for `FactoryHag\factory`, but I like to save
    typing.

Also, you can override the factory defaults. Here's how that test might look if
you did that:

    public function testUsernameEqualsWhateverItIsSetTo()
    {
      $user = Hag\f('user', array('username' => 'mclovin'));
      $this->assertEquals('mclovin', $user->username);
    }

## Limitations

  - Current functionality is limited to an extremely small subset of
    factory_girl.
      - There are no lazily initalized attributes, though I imagine this is
        possible with callbacks. It's something I'll probably get around to if I
        keep with it.
      - There is no equivalent to factory_girl's `build` or `attributes_for`.
        FactoryHag creates a record and saves it. I'm sure that will be trivial
        to implement, I just haven't needed it yet.
  - This isn't a problem with the 'Hag necessarily, but if PHPUnit errors out of
    a test for some reason, it can often leave the database in an unknown state.
    I guess this can probably be mitigated by doing some better setup in the
    PHPUnit test cases.

> PHP adaptation of some great concepts on which Rust was built.
# Oxidize
Rust encourages developers to handle errors and non-values properly by design.<br>
This leads to fewer non-logic bugs in production and the code might look cleaner as well.

The Oxidize library is written in pure PHP and has just *adapted* some concepts, which are also used in Rust. The method names are kept similar (snake_case to camelCase) for developers using both languages.<br>
There are some additional wrapper methods, such as `andThenContinue()` in both `Result` and `Option` to get rid of return statements. A new instance will then be returned automatically.

## Installation

```bash
composer require vadage/oxidize
```

## Exceptionless
Exceptions don't have to be declared to be thrown in a method, which increases the likelihood of them not being handled.<br>
This is where `Result`s come in handy with their representation of `Ok` and `Error`. Calling `unwrap` on `Error` or `unwrapError` on `Ok` will lead to a `ValueAccessError`.
```php
$userResult = $this->userRpc->login($email, $password);
if ($userResult->isOk()) {
    $user = $userResult->unwrap();
    $this->messages->queue(sprintf('Hello %1$s.', $user->getUsername()));
}
```

## Null safety
It may not always be obvious if a method returns an object or null.<br>
Instead of `null`, an `Option` can be used, which represents one of two states (`Some` and `None`). Calling `unwrap` on `None` will lead to a `ValueAccessError`.
```php
$terminationDateOption = $user->getTerminationDate();
if ($terminationDateOption->isSome()) {
    $terminationDate = $terminationDateOption->unwrap();
    $this->messages->queue(sprintf('Your login will be deactivated after %1$s.', $terminationDate->format(DateTimeInterface::RSS)));
}
```

## Monads for Result and Option
Monads can improve the codes aesthetics by getting rid of some `if` statements, variable declarations and `unwrap` calls.
```php
$this->userRpc->login($email, $password)->andThenContinue(function (User $user) {
    $username = $user->getUsername();

    $user->getTerminationDate()
        ->andThenContinue(function (DateTime $terminationDate) {
            $formattedDate = $terminationDate->format(DateTimeInterface::RSS);
            $this->messages->queue(sprintf('Hello %1$s. Your login will be deactivated after %2$s.', $username, $formattedDate));
        })
        ->orElseContinue(function () {
            $this->messages->queue(sprintf('Hello %1$s.', $username));
        });
});
```

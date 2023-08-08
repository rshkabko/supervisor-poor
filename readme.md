## Docs


```php
public function fields()
{
    return [
        Input::make('name', 'Name'),
        Input::make('email', 'Email')->type('email'),
        Input::make('password', 'Password')->type('password'),
    ];
}
```

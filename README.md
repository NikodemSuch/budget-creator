# Budget-Creator

**Purpose of this app is to help you with managing your finances.**

## Project setup

```
$ composer install
$ yarn install
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate -n
$ php bin/console doctrine:fixtures:load -n
```

## Project description

**Authentication**

App services are available only for authenticated users.
Users are authenticated using username (or email) and password.

**Accounts**

They represent real places for storing cash, for example *bank account*, *wallet*, *investments*.
Account has fixed currency.

**Budgets**

They represent purposes, for which account money should be allocated, for example *weekly budget*, *savings*.
Sum of budget balances is equal to sum of account balances. Balances can be negative.

**Transactions**

Transactions are used for cash flow.
When user creates a transaction, he chooses which account and budget it concerns.

**User Groups**

Members of a group have shared access between their accounts and budgets.

**Reports**

Report is a summary of account balances.
It is possible to generate summaries of various details, for example annual report with monthly expenses.

# Get balance

    GET /balances/{id}

Every user has a given balance of yellow and blue suns, which we've technically modelled like money: An amount and a currency (we'll use *unit* here):

## Parameters

| Name | Type     | Description                                               |
| ---- | -------- | --------------------------------------------------------- |
| `id` | `number` | The user id to get the balance for                        |

## Returns

```json
{
  "title" : "Balance",
  "type"  : "array",
  "items" : {
    "title"      : "Amount",
    "type"       : "object",
    "properties" : {
      "amount" : { "type" : "integer" },
      "unit"   : { "enum" : [ "blue", "yellow"] }
    }
  }
}
```

## Status codes

| Code  | Description                                               |
| ----- | --------------------------------------------------------- |
| `200` | When the account exists                                   |
| `404` | When the account by the given ID does not exist           |

## Examples

### First example

```http
GET /balances/1549

HTTP/1.1 200
Content-Type: application/json

[ { "amount" : 15 , "unit" : "blue" } , { "amount" : 10 , "unit" : "yellow" } ]
```

### When the user does not exist

```http
GET /balances/0

HTTP/1.1 404
Content-Type: application/json

{ "message" : "This account does not exist" }
```

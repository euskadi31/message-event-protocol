Message Event Protocol
======================

Message Event Protocol are a language-neutral extensible mechanism for serializing structured data in JSON.

Work In Progress

Target supported
----------------

* PHP 5.5
* PHP 7.1 (coming soon)
* TypeScript 2
* Go
* Java (coming soon)
* Swift 3 (coming soon)

Get Started
-----------

Generate PHP5 file:
```bash
./bin/mepc -o out_dir/ -t php5 MyEvent.mep
```

Generate Go file:
```bash
./bin/mepc -o out_dir/ -t go MyEvent.mep
```

Types supported
---------------

* String
* Boolean
* Integer
* Float
* DateTime
* Date
* Any

Example
-------

MyEvent.mep:
```
package Acme\Event;

option java_package = "com.acme.event";
option go_extends = "no";
option php_serializer = "native";

message User {
    required Integer id;
    required String email;
    optional String firstname;
    optional String lastname; 
}

message Device {
    required String type;
    optional String os;
    optional String osVersion;
    optional String brand;
    optional String manufacturer;
    optional String model;
    optional String serial;
}

message Context {
    required User user;
    required Device device;
}

message Event {
    required String id;
    required String type;
    required String action;
    required Context context;
    required Any payload;
    required DateTime createdAt;
    required DateTime sentAt;
}

message Size {
    required Integer width;
    required Integer height;
}

message PageViewPayload {
    required String url;
    required String referer;
    required String title;
    required Size screen;
    required Size viewport;
    required String encoding;
}

```


## License

message-event-protocol is licensed under [the MIT license](LICENSE.md).

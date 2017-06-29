# Atlas.Orm

> No annotations. No migrations. No lazy loading. No data-type abstractions.

Atlas is a [data mapper](http://martinfowler.com/eaaCatalog/dataMapper.html)
implementation for **persistence models** (*not* domain models).

As such, Atlas uses the term "record" to indicate that its objects are *not*
domain objects. Use Atlas records directly for simple data source interactions
at first. As a domain model grows within the application, use Atlas records to
populate domain objects. (Note that an Atlas record is a "passive" record, not an
[active record](http://martinfowler.com/eaaCatalog/activeRecord.html).
It is disconnected from the database.)

Atlas is stable for production use. Please send bug reports and pull requests!

Documentation is at <http://atlasphp.io>.

## Rationale

(Or, "Why does Atlas exist?")

Atlas helps developers to get started about as easily as they would with Active
Record for their *persistence* model, and provides a path to refactor more
easily towards a richer *domain* model as needed.

Atlas uses a table data gateway for the underlying table Rows, then composes
those Rows into Records and RecordSets via a data mapper. Simple methods can be
added, as they become necessary, to the Record and RecordSet persistence model
objects. (Rows do not have behavior.) The domain logic layer (e.g. a service
layer, application service, or use case) can then manipulate the Record objects.

A persistence model alone should get the appplication a long way, especially at
the beginning of a project. The Row, Record, and RecordSet objects are
disconnected from the database, which should make the refactoring and
integration process a lot cleaner than with Active Record.

As a domain model grows within the application, [Mehdi Khalili][mkap] shows that
the refactoring process can then move along one of two paths:

- "Domain Model composed of Persistence Model". That is, the domain objects
  use Atlas persistence model Record objects internally, but do not expose
  them. The domain objects can manipulate the persistence model objects
  internally as much as they wish. For example, a domain object might have a
  `getAddress()`method that reads from the internal Record.

- "DDD on top of ORM". Here, Repositories map the persistence model objects to
  and from domain objects. This provides a full decoupling of the domain model
  from the persistence model, but is more time-consuming to develop.

Finally, Atlas supports **composite primary keys** and **composite foreign
keys.** Performance in these cases is sure to be slower, but it is in fact
supported. (For some legacy systems, composite keys are absolutely necessary.)

[mkap]: http://www.mehdi-khalili.com/orm-anti-patterns-part-4-persistence-domain-model

Other rationalizations, essentially based around things *not* desired:

- No annotations. (Code should be in code, not in comments.)

- No migrations or other table-modification logic. Many ORMs read the PHP
  objects and create, or modify, tables from them. Atlas, on the other hand, is
  a *model* of the database, not a *creator* of it. (If migrations are needed,
  use a tool specifically for migrations.)

- No lazy-loading. Lazy-loading is seductive, but eventually is more trouble
  than it's worth. Atlas doesn't make it available at all, so it cannot be
  invoked accidentally.

- No data-type abstractions. Data-type abstraction seems great at first, but
  it too ends up not being worth the trouble. Therefore, the actual underlying
  database types are exposed and available as much as possible.

Possible deal-breakers for potential users:

- Atlas uses code generation, though only in a very limited way. It turns out
  that code generation is useful for building the SQL table classes. Each table
  is described as a PHP class, one that returns things like table name, column
  names, etc. This is the only substantial class that gets generated by Atlas;
  the others are empty extensions of parent classes.

- Atlas uses base Row, Record, and RecordSet classes, instead of plain-old PHP
  objects. If this were a domain modeling system, a base class would be
  unacceptable. Because Atlas is a *persistence* modeling system, base classes
  are less objectionable, but for some this may be undesired.

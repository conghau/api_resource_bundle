Guide Config
=========================
- Add config in `app\AppKernel`
```
...
   new \TCH\ApiResourceBundle\TCHApiResourceBundle(),
   new \Nelmio\ApiDocBundle\NelmioApiDocBundle(),
   new JMS\SerializerBundle\JMSSerializerBundle($this)

```
- Add config in `app\config\config.yml`
```
...
jms_serializer:
    metadata:
        auto_detection: true
nelmio_api_doc: ~
tch_api_resource:
   resources:
       user:
           entity: AppBundle\Entity\User
           actions: C.R.U.D.S #Create, Read/Retrieve, Update, Delete, Search
       .......... # add more here
```

- Add config in `app\config\routing.yml`
```
...
NelmioApiDocBundle:
    resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
    prefix:   /api/doc

tch_api_resource:
    resource: .
    type: tch_api_resource
    prefix:   /api/v1/

```
> Note: See more guide of nelmio [here](https://github.com/nelmio/NelmioApiDocBundle/blob/2.13.0/Resources/doc/index.rst)

- Usage trait class `TraitTCHRepository`
```
Example:
    use TCH\ApiResourceBundle\Util\TraitTCHRepository;
    
    /**
     * UserRepository
     *
     * This class was generated by the Doctrine ORM. Add your own custom
     * repository methods below.
     */
    class UserRepository extends \Doctrine\ORM\EntityRepository
    {
        use TraitTCHRepository;
    }

```

- Go to api doc
```
[host]/api/doc

Example: http://127.0.0.1:8000/api/doc

```

^^ Enjoy

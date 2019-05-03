[![Build Status](https://travis-ci.com/yeebase/Yeebase.Readiness.svg?branch=master)](https://travis-ci.com/yeebase/Yeebase.Readiness)
[![Latest Stable Version](https://poser.pugx.org/t3n/flow-healthstatus/v/stable)](https://packagist.org/packages/t3n/flow-healthstatus)
[![Total Downloads](https://poser.pugx.org/t3n/flow-healthstatus/downloads)](https://packagist.org/packages/t3n/flow-healthstatus)
[![License](https://poser.pugx.org/t3n/flow-healthstatus/license)](https://packagist.org/packages/t3n/flow-healthstatus)

# t3n.Flow.HealthStatus

Package to check the health status of a flow application.

It's extremly useful in a kubernetes environment to use with [readiness and liveness probe](https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-probes/)
to determine if a pod can serve traffic and if it is still alive.

## Usage

To determine the current health status of your application you can check wether the app is ready or still alive.

### Readiness

Simply execute the flow command `./flow app:isready`.

This will execute all tests defined in the `t3n.Flow.HealthStatus.testChain` of the Settings.yaml.
If all tests have passed, the `readyChain` tasks will be executed.

After a successfully run of the ready chain an internal lock will be set to prevent repeated execution,
so only the `readyChain` will be executed again. The `testChain` will be executed on every run.
So the readyChain should bring your application in an "ready state". Make sure to initialize everything you need.
The testChain should ping all services your application depends on.

### Liveness

Execute `./flow app:isalive` to check if your pod is still alive.

This will execute the `Yeebase.Readiness.livenessChain`.

Currently the liveness chain is empty by default and has one possible test: `statusCode`.

## Configuration

Add all your tests in the following format in your apps Settings.yaml:

```yaml
t3n:
  Flow:
    HealthStatus:
      testChain:
        yourUniqueTestKey:
          name: 'Optional name'
          test: 'database' // shorthand for a predefined task in t3n\Flow\HealthStatus\Test\*.Test or a full qualified class name
          options:
            key: 'value' // optional options for your test
          position: 'after otherTestKey' // optional position
```

After that, the check will execute the ready chain:

```yaml
t3n:
  Flow:
    HealthStatus:
      readyChain:
        yourUniqueTaskKey:
          name: 'Optional name'
          task: 'command' // shorthand for a predefined task in t3n\Flow\HealthStatus\Task\*.Task or a full qualified class name
          options:
            key: 'value' // optional options for your task
          position: 'after otherTaskKey' / optional position
          lockName: 'mylock' // optional lock override. This will create a lock for this task only and ignore the global lock
```

After a successful ready chain invokation, you can call `./flow app:isalive` to execute your liveness chain:

```yaml
t3n:
  Flow:
    HealthStatus:
      livenessChain:
        yourUniqueTestKey:
          name: 'Optional name'
          task: 'statusCode' // shorthand for a predefined task in t3n\Flow\HealthStatus\LivenessTest\*.Test or a full qualified class name
          options:
            key: 'value' // optional options for your task
          position: 'after otherTaskKey' / optional position
```

## Advanced configuration

Before each attempt to execute a ready task,
the check will test the `t3n.Flow.HealthStatus.defaultReadyTaskCondition` to see if the task should be executed.
In the default configuration this is simply a check to see if the ready lock is not yet set.

You can override this behaviour on a per task basis:

```yaml
t3n:
  Flow:
    HealthStatus:
      readyChain:
        yourUniqueTaskKey:
          condition: '${Lock.isSet("mylock")}' // this can be any eel expression
          afterInvocation: '${Lock.set("mylock")}' // this will be executed after a successfull invocation
```

_(the `lockName` setting is simply a shorthand for exactly this example)_

To extend the eel context, you can provide additional helpers in `t3n.Flow.HealthStatus.defaultContext`.

## Example Configuration

This example could be used in your Flow package to make sure that your application pod has a ready state
to serve traffic. Therefore it will always check the ping status for doctrine, redis and beanstalk.
On the first run all missing database migrations will be executed, the redis cache flushed and static resources published.
After a successfull run only the testChain will be executed again.

```yaml
t3n:
  Flow:
    HealthStatus:
      testChain:
        database:
          test: doctrine
          position: start
        redis:
          test: redis
          options:
            hostmane: your-redis-host
        beanstalk:
          test: beanstalk
          options:
            hostname: your-beanstalk-host
      readyChain:
        migrations:
          task: command
          options:
            command: 'neos.flow:doctrine:migrate'
        flushRedis:
          name: 'Flush redis'
          position: 'start 100'
          task: redis
          options:
            hostname: your-redis-host
            command: FLUSHDB
            database: 0
        staticResources:
          name: 'Publish static resources'
          task: command
          position: 'end 20'
          lockname: staticresources
          cacheName: t3n_FlowHealthStatus_LocalLock
          options:
            command: 'neos.flow:resource:publish'
            arguments:
              collection: static
      livenessChain:
        home:
          task: statusCode
          name: 'Homepage responds'
          options:
            url: '/'
            method: 'GET'
            statusCode: 200
```

Note the `lockname` configuration. This Configuration enables you to run tasks only once per deployment or always.
By default the `t3n_FlowHealthStatus_Lock` cache is used to read and write locks. Add this to your Caches.yaml and all your application pods will
rely on the same lock files as they don't use the local file storage but redis. This will result in a execution once per deployment:

```yaml
t3n_FlowHealthStatus_Lock:
  backend: Neos\Cache\Backend\RedisBackend
  backendOptions:
    hostname: 'your-redis-server'
    database: 2
```

The `staticResources` task has a custom cacheName configured. To ensure that this task will be executed in each application pod set it to local file storage:

```yaml
t3n_FlowHealthStatus_LocalLock:
  frontend: Neos\Cache\Frontend\StringFrontend
  backend: Neos\Cache\Backend\FileBackend
```

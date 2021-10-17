# k8s

Para hacer deployment de un overlay, hay que correr

```shell
kustomize build --enable-alpha-plugins ${OVERLAY} | kubectl apply -f -
```

esto es necesario porque `kubectl` no usa los plugins de `kustomize`.

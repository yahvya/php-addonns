# Utilitaire de limitation d'appel

> Cet utilitaire n'a aucune notice d'intégration supplémentaire

## Utilisation

> L'utilisation de cet utilitaire passe par l'attribut **AttemptLimitable** ainsi que la classe ***AttemptLimiter***.
> L'attribut sert à décrire les conditions de blocage et la classe grâce à l'appel de la fonction ***attempt*** permet d'appeler la fonction fournie de manière normale en ajoutant la couche de validation d'appel.

### Paramètres de l'attribut

- "countOfAttempt" nombre de tentatives autorisées. Si 2 est fourni, la troisième tentative échouera.
- "timeBeforeNextAttempt" nombre de secondes avant d'autoriser une nouvelle série de tentatives.
- "errorMarker" enumération servant à décrire comment reconnaitre une erreur de la fonction
- "resetOnSuccess" optionnel, permet de décrire si le décompte des séries est remis à 0 après un appel réussi

### Fonction attempt

> Par défaut la fonction attempt se charge de retourner la valeur de retour de votre fonction, mais aussi de renvoyer une "l'exception" renvoyée par votre fonction.

- "callable" tableau représentant la méthode à appeler
- "args" optionnel, tableau contenant les arguments de la méthode
- "onError", une exception est renvoyé par défaut quand une tentative est faites pendant que c'est bloqué. Si "onError" ne vaut pas null, cette fonction sera appellé et son retour sera retourné

**Classe dont l'appel d'une méthode est à limiter**

```
use PhpAddons\AttemptLimiter\AttemptErrorMarker;
use PhpAddons\AttemptLimiter\AttemptLimitable;

class TestClass{
    #[AttemptLimitable(
        countOfAttempt: 2,
        timeBeforeNextAttempt: 3600,
        errorMarker: AttemptErrorMarker::FALSE_RETURNED,
        resetOnSuccess: true
    )]
    public static void testFunction(string $name):bool{
        return false;
    }
}
```

**Appel de la méthode**

```
use PhpAddons\AttemptLimiter\AttemptLimiter;

AttemptLimiter::attempt(callable: [TestClass::class,"testFunction"],args: ["yahvya"]);
AttemptLimiter::attempt(callable: [TestClass::class,"testFunction"],args: ["yahvya"],onError: function(){ echo "erreur" });
```


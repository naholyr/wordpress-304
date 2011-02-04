Le code présenté ici est un plugin wordpress. À déposer dans un sous-dossier de "wp-content/plugins" pour l'installer. L'article ci-dessous décrit le fonctionnement général du statut 304, et aborde l'implémentation de ce plugin en particulier.

"304 - Not Modified" est votre ami
==================================

Spéciale dédicace à Romain (aka Palleas), sans l'obstination de qui tout cela n'aurait pas été rendu possible :')

J'ai commis un petit plugin Wordpress (activé ici-même) qui permet d'optimiser la mise en cache du navigateur et de reposer d'autant le serveur :) Plusieurs ingrédients permettent cette opération, et surtout la rendent intéressante:

* La fréquence de publication est faible, il y a donc de très fortes chances qu'un visiteur revoit le même contenu à chaque fois qu'il ira sur le blog.
* Le blog ne s'encombre pas d'éléments dynamiques hors des posts, pages, et commentaires. La "date de dernière modification" globale est donc très facile à déterminer: c'est la plus grande des dates de dernière modification de chacun de ces types d'éléments.

Partant de là ce plugin va donc implémenter correctement ce qu'on oublie trop souvent de faire :)

1. On est le 8 février 2011, il est 12:00, et le navigateur vient sur le site *tadaaaam*
2. Le navigateur arrive et dit "coucou, j'ai en cache une version de ta page datée du 25 janvier 2011 à 23:11" ("If-Modified-Since: 2011-01-25 23:11").
3. Le serveur voit ça arriver, il fait ses deux petites requêtes et voit que la date max de dernière modification est le 5 février à 11:13, il répond donc: "OK, et ben y a du nouveau, ça date du 5 février à 11:13, et voilà tout le contenu, mais comme j'ai bossé pour te générer, tu seras gentil de le mettre de côté steuplay" ("200 OK" & "Last-Modified: 2011-02-05 11:13" + tout le contenu).
4. Le navigateur revient 2 jours plus tard, et dit donc "coucou, j'ai une version de ta page datée du 5 février à 11:13".
5. Ce à quoi le serveur répond cette fois: "OK, rien de neuf, démerde-toi avec ce que tu as en cache" ("304 Not Modified" + rien d'autre).

Le serveur travaille moins, donc économise de l'énergie, donc sauve des bébés phoques. De plus il répond plus vite, donc vous permet de passer plus de temps à vous occuper de votre famille et participe donc au bien-être des familles.

Implémentez le code de retour 304. Ce n'est parfois pas possible (ou plutôt trop coûteux) d'avoir cette information de la date de dernière modification permettant de répondre correctement à la question posée par le navigateur, et je pense que c'est pourquoi beaucoup ignorent tout simplement cette possibilité (certains croyaient même qu'il était impossible de faire retourner une 304 à une page web, hein Medhi !).

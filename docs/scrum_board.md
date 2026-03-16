# Scrum board (proposition)

## Epic: Authentification (Sanctum)
- US1: En tant qu'utilisateur, je peux m'inscrire via `/api/register` et recevoir un token.
- US2: En tant qu'utilisateur, je peux me connecter via `/api/login` et recevoir un token.
- US3: En tant qu'utilisateur, je peux me déconnecter via `/api/logout` (token révoqué).
- US4: En tant qu'utilisateur authentifié, je peux récupérer mon profil via `/api/user`.

## Epic: Gestion des catégories
- US5: En tant qu'admin, je peux créer une catégorie via `/api/categories`.
- US6: En tant qu'admin, je peux lister mes catégories via `/api/categories`.
- US7: En tant qu'admin, je peux consulter/modifier/supprimer une catégorie via `/api/categories/{id}`.
- US8: En tant qu'admin, je peux associer des plats à une catégorie via `/api/categories/{id}/plats`.

## Epic: Gestion des plats
- US9: En tant qu'admin, je peux créer un plat via `/api/plats`.
- US10: En tant qu'admin, je peux lister mes plats via `/api/plats`.
- US11: En tant qu'admin, je peux consulter/modifier/supprimer un plat via `/api/plats/{id}`.

## Contraintes techniques
- T1: Policies pour garantir l'accès uniquement aux ressources appartenant au user.
- T2: Postman collection + scénarios (happy path + forbidden + validation).
- T3: Documentation OpenAPI (Swagger).


# Demo data

I dati demo servono solo per prove manuali in locale o sviluppo.

## Caricamento

Esegui:

```bash
php artisan demo:seed
```

Il comando crea o aggiorna solo dati con codici pratica `DEMO-*`, anni demo, opzioni select demo e impostazioni azienda solo se non esistono gia impostazioni aziendali.

## Protezione produzione

`demo:seed` rifiuta l'esecuzione in produzione. L'opzione `--force` esiste solo per casi eccezionali e consapevoli:

```bash
php artisan demo:seed --force
```

Non usarla su database reali.

## Reset locale

Per ricreare un database locale da zero:

```bash
php artisan migrate:fresh --seed
php artisan demo:seed
```

`migrate:fresh` elimina tutte le tabelle: usalo solo in locale.

## Verifica manuale

- Apri `admin/travel-records` e cerca codici `DEMO-*`.
- Apri `admin/fatturazione`, seleziona anno corrente e mese corrente.
- Esporta PDF/Excel e verifica righe e totali demo.
- Usa "Prepara email" e verifica il testo del riepilogo.
- Accedi come Operatore e prova a modificare un record dell'anno precedente bloccato.

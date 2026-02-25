---
name: commit
description: Commit changes with a descriptive message based on the actual diff
---

Crea un commit con un mensaje descriptivo basado en los cambios reales del código.

## Reglas

- El mensaje de commit NUNCA puede ser "autocommit" ni variantes como "auto commit", "auto-commit", etc.
- El mensaje debe describir QUE se cambió y POR QUÉ, basándose en el diff real.
- Usa español para el mensaje de commit.
- Sé conciso pero específico (1-2 líneas máximo).

## Proceso

1. Ejecuta `git status` para ver el estado actual.
2. Ejecuta `git diff` y `git diff --cached` para analizar los cambios.
3. Si se pasaron argumentos ($ARGUMENTS), usa esos archivos específicos. Si no, incluye todos los archivos modificados.
4. Analiza el diff y redacta un mensaje descriptivo que explique:
   - Qué archivos/funciones/clases se modificaron
   - Qué tipo de cambio es (nueva funcionalidad, corrección, refactor, etc.)
   - Breve descripción del propósito del cambio
5. Agrega los archivos al staging con `git add` (archivos específicos o los modificados).
6. Haz el commit con el mensaje generado. Usa HEREDOC para el mensaje:

```bash
git commit -m "$(cat <<'EOF'
Mensaje descriptivo aquí

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
EOF
)"
```

7. Muestra el resultado del commit al usuario.

## Validación

Antes de hacer el commit, verifica que:
- El mensaje NO contiene la palabra "autocommit"
- El mensaje describe cambios reales del diff
- Hay archivos para commitear (no hacer commits vacíos)

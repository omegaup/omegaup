#!/bin/bash

PWD="$(dirname "$(realpath "${0}")")"

if [[ -z "$1" ]]; then
  echo "Usage: $0 <location>"
  exit 1
fi

location="$1"
subscription="${subscription:-9fc6c11d-9406-42f8-9a78-3813ed0875fa}"
resource_group="omegaup-runner-${location}"

if [[ "$(az group show \
           --resource-group "${resource_group}" \
           --query 'location' \
           --subscription "${subscription}" \
           --output=tsv 2>/dev/null || true)" != "${location}" ]]; then
  echo "Deleting old resource group..."
  az group delete \
    --name "${resource_group}" \
    --subscription "${subscription}" \
    --yes || true
  echo "Creating resource group..."
  az group create \
    --name "${resource_group}" \
    --location "${location}" \
    --subscription "${subscription}"
fi

echo "deploying resource group..."
az deployment group create \
  --resource-group "${resource_group}" \
  --template-file "${PWD}/omegaup-runner-template.json" \
  --parameter "customData=$(base64 --wrap=0 "${PWD}/cloud-init.yml")" \
  --subscription "${subscription}"

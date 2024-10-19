up:
	docker compose up -d --build --remove-orphans
reset:
	docker compose exec app rr reset
bash:
	docker compose exec app sh
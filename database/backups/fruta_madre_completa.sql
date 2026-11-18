--
-- PostgreSQL database dump
--

\restrict dumbxOPn8Yw9JmnJ7Hd48Zy5YORDQYxApWvIrvCj0cvKuy5hi3BLbEhS4Q267AW

-- Dumped from database version 16.14 (Homebrew)
-- Dumped by pg_dump version 16.14 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: carrito_snack; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.carrito_snack (
    id_carrito integer NOT NULL,
    codigo_inventario character varying(50),
    estado_fisico character varying(50)
);


ALTER TABLE public.carrito_snack OWNER TO postgres;

--
-- Name: carrito_snack_id_carrito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.carrito_snack_id_carrito_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.carrito_snack_id_carrito_seq OWNER TO postgres;

--
-- Name: carrito_snack_id_carrito_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.carrito_snack_id_carrito_seq OWNED BY public.carrito_snack.id_carrito;


--
-- Name: categoria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categoria (
    id_categoria integer NOT NULL,
    nombre_categoria character varying(255),
    descripcion text,
    activa boolean
);


ALTER TABLE public.categoria OWNER TO postgres;

--
-- Name: categoria_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categoria_id_categoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categoria_id_categoria_seq OWNER TO postgres;

--
-- Name: categoria_id_categoria_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categoria_id_categoria_seq OWNED BY public.categoria.id_categoria;


--
-- Name: detalle_pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.detalle_pedido (
    id_detalle_pedido integer NOT NULL,
    id_pedido integer NOT NULL,
    id_producto integer NOT NULL,
    cantidad integer,
    precio_unitario numeric(10,2),
    subtotal_linea numeric(10,2)
);


ALTER TABLE public.detalle_pedido OWNER TO postgres;

--
-- Name: detalle_pedido_id_detalle_pedido_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.detalle_pedido_id_detalle_pedido_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.detalle_pedido_id_detalle_pedido_seq OWNER TO postgres;

--
-- Name: detalle_pedido_id_detalle_pedido_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.detalle_pedido_id_detalle_pedido_seq OWNED BY public.detalle_pedido.id_detalle_pedido;


--
-- Name: direccion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.direccion (
    id_direccion integer NOT NULL,
    id_usuario integer NOT NULL,
    calle_numero character varying(255),
    colonia character varying(255),
    ciudad character varying(255),
    codigo_postal character varying(10),
    referencias text
);


ALTER TABLE public.direccion OWNER TO postgres;

--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.direccion_id_direccion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.direccion_id_direccion_seq OWNER TO postgres;

--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.direccion_id_direccion_seq OWNED BY public.direccion.id_direccion;


--
-- Name: entrega; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.entrega (
    id_entrega integer NOT NULL,
    id_pedido integer NOT NULL,
    id_direccion integer NOT NULL,
    fecha_estimada timestamp without time zone,
    estado_entrega character varying(50),
    notas_repartidor text
);


ALTER TABLE public.entrega OWNER TO postgres;

--
-- Name: entrega_id_entrega_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.entrega_id_entrega_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.entrega_id_entrega_seq OWNER TO postgres;

--
-- Name: entrega_id_entrega_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.entrega_id_entrega_seq OWNED BY public.entrega.id_entrega;


--
-- Name: evento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evento (
    id_evento integer NOT NULL,
    id_usuario integer NOT NULL,
    id_paquete integer NOT NULL,
    tipo_evento character varying(100),
    fecha_hora timestamp without time zone,
    lugar_direccion character varying(255),
    num_invitados integer,
    observaciones text
);


ALTER TABLE public.evento OWNER TO postgres;

--
-- Name: evento_extra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evento_extra (
    id_evento_extra integer NOT NULL,
    id_evento integer NOT NULL,
    id_extra integer NOT NULL,
    precio_aplicado numeric(10,2) NOT NULL
);


ALTER TABLE public.evento_extra OWNER TO postgres;

--
-- Name: evento_extra_id_evento_extra_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evento_extra_id_evento_extra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evento_extra_id_evento_extra_seq OWNER TO postgres;

--
-- Name: evento_extra_id_evento_extra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.evento_extra_id_evento_extra_seq OWNED BY public.evento_extra.id_evento_extra;


--
-- Name: evento_id_evento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.evento_id_evento_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.evento_id_evento_seq OWNER TO postgres;

--
-- Name: evento_id_evento_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.evento_id_evento_seq OWNED BY public.evento.id_evento;


--
-- Name: extra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.extra (
    id_extra integer NOT NULL,
    nombre_extra character varying(255),
    descripcion text,
    precio_adicional numeric(10,2)
);


ALTER TABLE public.extra OWNER TO postgres;

--
-- Name: extra_id_extra_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.extra_id_extra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.extra_id_extra_seq OWNER TO postgres;

--
-- Name: extra_id_extra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.extra_id_extra_seq OWNED BY public.extra.id_extra;


--
-- Name: notificacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notificacion (
    id_notificacion integer NOT NULL,
    id_usuario integer NOT NULL,
    titulo character varying(255),
    mensaje text,
    fecha_envio timestamp without time zone,
    estado_leida boolean
);


ALTER TABLE public.notificacion OWNER TO postgres;

--
-- Name: notificacion_id_notificacion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notificacion_id_notificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notificacion_id_notificacion_seq OWNER TO postgres;

--
-- Name: notificacion_id_notificacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notificacion_id_notificacion_seq OWNED BY public.notificacion.id_notificacion;


--
-- Name: pago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pago (
    id_pago integer NOT NULL,
    id_pedido integer NOT NULL,
    metodo_pago character varying(50),
    estado_pago character varying(50),
    monto_pagado numeric(10,2),
    fecha_pago timestamp without time zone
);


ALTER TABLE public.pago OWNER TO postgres;

--
-- Name: pago_id_pago_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pago_id_pago_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pago_id_pago_seq OWNER TO postgres;

--
-- Name: pago_id_pago_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pago_id_pago_seq OWNED BY public.pago.id_pago;


--
-- Name: paquete; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.paquete (
    id_paquete integer NOT NULL,
    nombre_paquete character varying(255),
    descripcion text,
    capacidad_personas integer,
    duracion_horas integer,
    precio_base numeric(10,2)
);


ALTER TABLE public.paquete OWNER TO postgres;

--
-- Name: paquete_extra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.paquete_extra (
    id_paquete_extra integer NOT NULL,
    id_paquete integer NOT NULL,
    id_extra integer NOT NULL
);


ALTER TABLE public.paquete_extra OWNER TO postgres;

--
-- Name: paquete_extra_id_paquete_extra_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.paquete_extra_id_paquete_extra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.paquete_extra_id_paquete_extra_seq OWNER TO postgres;

--
-- Name: paquete_extra_id_paquete_extra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.paquete_extra_id_paquete_extra_seq OWNED BY public.paquete_extra.id_paquete_extra;


--
-- Name: paquete_id_paquete_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.paquete_id_paquete_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.paquete_id_paquete_seq OWNER TO postgres;

--
-- Name: paquete_id_paquete_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.paquete_id_paquete_seq OWNED BY public.paquete.id_paquete;


--
-- Name: paquete_producto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.paquete_producto (
    id_paquete_producto integer NOT NULL,
    id_paquete integer NOT NULL,
    id_producto integer NOT NULL,
    cantidad_incluida integer
);


ALTER TABLE public.paquete_producto OWNER TO postgres;

--
-- Name: paquete_producto_id_paquete_producto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.paquete_producto_id_paquete_producto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.paquete_producto_id_paquete_producto_seq OWNER TO postgres;

--
-- Name: paquete_producto_id_paquete_producto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.paquete_producto_id_paquete_producto_seq OWNED BY public.paquete_producto.id_paquete_producto;


--
-- Name: pedido; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pedido (
    id_pedido integer NOT NULL,
    id_usuario integer NOT NULL,
    fecha_pedido timestamp without time zone,
    estado_pedido character varying(50),
    subtotal numeric(10,2),
    total numeric(10,2),
    tipo_servicio character varying(20),
    fecha_preferida date,
    horario_preferido character varying(20),
    observaciones text,
    correo_contacto character varying(180)
);


ALTER TABLE public.pedido OWNER TO postgres;

--
-- Name: pedido_id_pedido_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pedido_id_pedido_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pedido_id_pedido_seq OWNER TO postgres;

--
-- Name: pedido_id_pedido_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pedido_id_pedido_seq OWNED BY public.pedido.id_pedido;


--
-- Name: producto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.producto (
    id_producto integer NOT NULL,
    id_categoria integer NOT NULL,
    nombre character varying(255),
    descripcion text,
    ingredientes text,
    precio numeric(10,2),
    imagen_url character varying(255),
    stock_disponible integer,
    activo boolean,
    sku character varying(30) NOT NULL,
    costo numeric(10,2) DEFAULT 0 NOT NULL,
    stock_minimo integer DEFAULT 5 NOT NULL,
    permite_paquetes boolean DEFAULT false NOT NULL,
    subcategoria character varying(120),
    actualizado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_producto_costo CHECK ((costo >= (0)::numeric)),
    CONSTRAINT chk_producto_stock_minimo CHECK ((stock_minimo >= 0))
);


ALTER TABLE public.producto OWNER TO postgres;

--
-- Name: producto_id_producto_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.producto_id_producto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_id_producto_seq OWNER TO postgres;

--
-- Name: producto_id_producto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.producto_id_producto_seq OWNED BY public.producto.id_producto;


--
-- Name: reserva; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reserva (
    id_reserva integer NOT NULL,
    id_evento integer NOT NULL,
    id_carrito integer,
    estado_reserva character varying(50),
    costo_total numeric(10,2)
);


ALTER TABLE public.reserva OWNER TO postgres;

--
-- Name: reserva_id_reserva_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reserva_id_reserva_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reserva_id_reserva_seq OWNER TO postgres;

--
-- Name: reserva_id_reserva_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reserva_id_reserva_seq OWNED BY public.reserva.id_reserva;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuario (
    id_usuario integer NOT NULL,
    nombre_completo character varying(255),
    correo character varying(255),
    telefono character varying(20),
    contrasena_hash character varying(255),
    rol character varying(50),
    fecha_registro timestamp without time zone
);


ALTER TABLE public.usuario OWNER TO postgres;

--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuario_id_usuario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuario_id_usuario_seq OWNER TO postgres;

--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuario_id_usuario_seq OWNED BY public.usuario.id_usuario;


--
-- Name: carrito_snack id_carrito; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carrito_snack ALTER COLUMN id_carrito SET DEFAULT nextval('public.carrito_snack_id_carrito_seq'::regclass);


--
-- Name: categoria id_categoria; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria ALTER COLUMN id_categoria SET DEFAULT nextval('public.categoria_id_categoria_seq'::regclass);


--
-- Name: detalle_pedido id_detalle_pedido; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_pedido ALTER COLUMN id_detalle_pedido SET DEFAULT nextval('public.detalle_pedido_id_detalle_pedido_seq'::regclass);


--
-- Name: direccion id_direccion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion ALTER COLUMN id_direccion SET DEFAULT nextval('public.direccion_id_direccion_seq'::regclass);


--
-- Name: entrega id_entrega; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrega ALTER COLUMN id_entrega SET DEFAULT nextval('public.entrega_id_entrega_seq'::regclass);


--
-- Name: evento id_evento; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento ALTER COLUMN id_evento SET DEFAULT nextval('public.evento_id_evento_seq'::regclass);


--
-- Name: evento_extra id_evento_extra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento_extra ALTER COLUMN id_evento_extra SET DEFAULT nextval('public.evento_extra_id_evento_extra_seq'::regclass);


--
-- Name: extra id_extra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.extra ALTER COLUMN id_extra SET DEFAULT nextval('public.extra_id_extra_seq'::regclass);


--
-- Name: notificacion id_notificacion; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notificacion ALTER COLUMN id_notificacion SET DEFAULT nextval('public.notificacion_id_notificacion_seq'::regclass);


--
-- Name: pago id_pago; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pago ALTER COLUMN id_pago SET DEFAULT nextval('public.pago_id_pago_seq'::regclass);


--
-- Name: paquete id_paquete; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete ALTER COLUMN id_paquete SET DEFAULT nextval('public.paquete_id_paquete_seq'::regclass);


--
-- Name: paquete_extra id_paquete_extra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_extra ALTER COLUMN id_paquete_extra SET DEFAULT nextval('public.paquete_extra_id_paquete_extra_seq'::regclass);


--
-- Name: paquete_producto id_paquete_producto; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_producto ALTER COLUMN id_paquete_producto SET DEFAULT nextval('public.paquete_producto_id_paquete_producto_seq'::regclass);


--
-- Name: pedido id_pedido; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id_pedido SET DEFAULT nextval('public.pedido_id_pedido_seq'::regclass);


--
-- Name: producto id_producto; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto ALTER COLUMN id_producto SET DEFAULT nextval('public.producto_id_producto_seq'::regclass);


--
-- Name: reserva id_reserva; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva ALTER COLUMN id_reserva SET DEFAULT nextval('public.reserva_id_reserva_seq'::regclass);


--
-- Name: usuario id_usuario; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id_usuario SET DEFAULT nextval('public.usuario_id_usuario_seq'::regclass);


--
-- Data for Name: carrito_snack; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.carrito_snack (id_carrito, codigo_inventario, estado_fisico) FROM stdin;
\.


--
-- Data for Name: categoria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categoria (id_categoria, nombre_categoria, descripcion, activa) FROM stdin;
6	Antojitos frutales	Fruta fresca preparada con diferentes ingredientes y toppings.	t
7	Dulces	Postres, dulces y antojos para compartir.	t
8	Salados	Botanas y antojos salados preparados al momento.	t
9	Pasteles	Pasteles para celebraciones y ocasiones especiales.	t
10	Arreglos florales	Arreglos florales para regalar y celebrar.	t
11	Carritos de snacks	Servicios de carritos de snacks para eventos.	t
\.


--
-- Data for Name: detalle_pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.detalle_pedido (id_detalle_pedido, id_pedido, id_producto, cantidad, precio_unitario, subtotal_linea) FROM stdin;
1	1	20	1	65.00	65.00
2	1	21	1	75.00	75.00
3	1	24	2	55.00	110.00
4	2	20	1	65.00	65.00
5	3	20	1	65.00	65.00
6	3	21	2	75.00	150.00
7	4	20	1	65.00	65.00
8	4	21	2	75.00	150.00
9	5	21	1	75.00	75.00
10	6	20	1	65.00	65.00
11	6	21	1	75.00	75.00
12	7	26	1	85.00	85.00
13	8	20	1	65.00	65.00
14	9	20	1	65.00	65.00
15	10	20	2	65.00	130.00
16	10	21	4	75.00	300.00
17	11	23	2	65.00	130.00
18	12	21	3	75.00	225.00
19	13	20	1	65.00	65.00
20	14	20	2	65.00	130.00
21	14	26	2	85.00	170.00
\.


--
-- Data for Name: direccion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.direccion (id_direccion, id_usuario, calle_numero, colonia, ciudad, codigo_postal, referencias) FROM stdin;
1	1	7a avenida sur 37 entre 14 y 16 ote	\N	\N	\N	carca de la unach
2	1	12 avenida sur	\N	\N	\N	cerca de akishino
3	1	20 calle pie	\N	\N	\N	cerca de la unach
4	1	7a avenida sur	\N	\N	\N	casa color rosa
5	1	7a avenida sur 37	\N	\N	\N	cerca de la secundaria
6	1	20 calle poniente	\N	\N	\N	cerca de pemex
7	1	20 sur	\N	\N	\N	casa blanca
8	3	12 avenida	\N	\N	\N	roja
9	3	12 avenida	\N	\N	\N	roja
10	3	20 avenida sur entre 15 y 14 oriente	\N	\N	\N	cerca unach
11	3	12 avenida	\N	\N	\N	rojo
12	1	akishino	\N	\N	\N	cerca de Kafeto
\.


--
-- Data for Name: entrega; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.entrega (id_entrega, id_pedido, id_direccion, fecha_estimada, estado_entrega, notas_repartidor) FROM stdin;
1	1	1	\N	pendiente	\N
2	3	2	\N	pendiente	\N
3	4	3	\N	pendiente	\N
4	5	4	\N	pendiente	\N
5	6	5	\N	pendiente	\N
6	7	6	\N	pendiente	\N
7	8	7	\N	pendiente	sin limon
8	9	8	\N	pendiente	no lleguen tarde
9	10	9	\N	pendiente	nada
10	12	10	\N	pendiente	xd
11	13	11	\N	pendiente	\N
12	14	12	\N	pendiente	cerca de la universidad
\.


--
-- Data for Name: evento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evento (id_evento, id_usuario, id_paquete, tipo_evento, fecha_hora, lugar_direccion, num_invitados, observaciones) FROM stdin;
\.


--
-- Data for Name: evento_extra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evento_extra (id_evento_extra, id_evento, id_extra, precio_aplicado) FROM stdin;
\.


--
-- Data for Name: extra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.extra (id_extra, nombre_extra, descripcion, precio_adicional) FROM stdin;
1	Barra extra de fruta	Más fruta fresca preparada al momento.	450.00
2	Gomitas enchiladas y pulpas	Dulces, pulpas y botanas con chilito.	350.00
3	Hora extra de carrito	Una hora más de servicio.	600.00
4	Letrero personalizado	Un detalle decorativo para tu evento.	280.00
\.


--
-- Data for Name: notificacion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notificacion (id_notificacion, id_usuario, titulo, mensaje, fecha_envio, estado_leida) FROM stdin;
\.


--
-- Data for Name: pago; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pago (id_pago, id_pedido, metodo_pago, estado_pago, monto_pagado, fecha_pago) FROM stdin;
1	1	efectivo	pendiente	0.00	\N
2	2	efectivo	pendiente	0.00	\N
3	3	efectivo	pendiente	0.00	\N
4	4	efectivo	pendiente	0.00	\N
5	5	tarjeta	pendiente	0.00	\N
6	6	tarjeta	pendiente	0.00	\N
7	7	tarjeta	pendiente	0.00	\N
8	8	transferencia	pendiente	0.00	\N
9	9	efectivo	pendiente	0.00	\N
10	10	efectivo	pendiente	0.00	\N
11	11	tarjeta	pendiente	0.00	\N
12	12	efectivo	pendiente	0.00	\N
13	13	efectivo	pendiente	0.00	\N
14	14	transferencia	pendiente	0.00	\N
\.


--
-- Data for Name: paquete; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.paquete (id_paquete, nombre_paquete, descripcion, capacidad_personas, duracion_horas, precio_base) FROM stdin;
1	Paquete Pachanga	Servicio de carrito de fruta para eventos	50	3	3450.00
\.


--
-- Data for Name: paquete_extra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.paquete_extra (id_paquete_extra, id_paquete, id_extra) FROM stdin;
1	1	1
2	1	2
3	1	3
4	1	4
\.


--
-- Data for Name: paquete_producto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.paquete_producto (id_paquete_producto, id_paquete, id_producto, cantidad_incluida) FROM stdin;
\.


--
-- Data for Name: pedido; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pedido (id_pedido, id_usuario, fecha_pedido, estado_pedido, subtotal, total, tipo_servicio, fecha_preferida, horario_preferido, observaciones, correo_contacto) FROM stdin;
1	1	2026-10-18 02:54:02.955469	pendiente	250.00	250.00	\N	\N	\N	\N	\N
2	1	2026-10-18 04:03:02.014438	pendiente	65.00	65.00	\N	\N	\N	\N	\N
3	1	2026-10-20 16:10:08.547857	pendiente	215.00	215.00	\N	\N	\N	\N	\N
4	1	2026-10-20 16:41:56.527632	pendiente	215.00	215.00	\N	\N	\N	\N	\N
5	1	2026-10-20 16:47:09.241316	pendiente	75.00	75.00	\N	\N	\N	\N	\N
6	1	2026-10-25 00:25:18.652233	pendiente	140.00	140.00	\N	\N	\N	\N	\N
7	1	2026-10-25 00:31:56.833608	pendiente	85.00	85.00	\N	\N	\N	\N	\N
8	1	2026-11-11 21:43:45.102222	pendiente	65.00	65.00	\N	\N	\N	\N	\N
9	3	2026-11-11 23:27:01.684901	pendiente	65.00	65.00	\N	\N	\N	\N	\N
10	3	2026-11-12 01:17:00.393199	pendiente	430.00	430.00	\N	\N	\N	\N	\N
11	3	2026-11-12 01:21:34.693487	pendiente	130.00	130.00	\N	\N	\N	\N	\N
12	3	2026-11-12 06:26:39.496928	pendiente	225.00	225.00	\N	\N	\N	\N	\N
13	3	2026-11-12 12:27:08.712677	pendiente	65.00	65.00	\N	\N	\N	\N	\N
14	1	2026-11-17 21:05:55.298466	pendiente	300.00	300.00	domicilio	2026-11-25	10:00-12:00	cerca de la universidad	orobertocarlos80@gmail.com
\.


--
-- Data for Name: producto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.producto (id_producto, id_categoria, nombre, descripcion, ingredientes, precio, imagen_url, stock_disponible, activo, sku, costo, stock_minimo, permite_paquetes, subcategoria, actualizado_en) FROM stdin;
22	6	Fresas con crema	Fresas frescas acompañadas de crema dulce.	Fresas, crema y azúcar.	80.00	\N	15	t	FM-PRD-0022	0.00	5	f	\N	2026-11-17 21:18:12.707796
25	7	Brocheta de fruta con chocolate	Brocheta de fruta fresca bañada con chocolate.	Fresa, plátano, uva y chocolate.	70.00	\N	15	t	FM-PRD-0025	0.00	5	f	\N	2026-11-17 21:18:12.707796
27	8	Papas preparadas	Papas preparadas con salsas y toppings.	Papas, chamoy, chile, limón y salsas.	70.00	\N	15	t	FM-PRD-0027	0.00	5	f	\N	2026-11-17 21:18:12.707796
28	8	Nachos Fruta Madre	Nachos preparados para compartir.	Totopos, queso, jalapeño, crema y salsas.	75.00	\N	15	t	FM-PRD-0028	0.00	5	f	\N	2026-11-17 21:18:12.707796
29	8	Chicharrón preparado	Chicharrón preparado con ingredientes frescos.	Chicharrón, col, crema, queso, salsa y cueritos.	80.00	\N	12	t	FM-PRD-0029	0.00	5	f	\N	2026-11-17 21:18:12.707796
30	9	Pastel de chocolate	Pastel de chocolate para celebraciones.	Pan de chocolate, relleno y cobertura de chocolate.	450.00	\N	5	t	FM-PRD-0030	0.00	5	f	\N	2026-11-17 21:18:12.707796
31	9	Pastel de vainilla con fresas	Pastel de vainilla decorado con fresas.	Pan de vainilla, crema y fresas.	480.00	\N	5	t	FM-PRD-0031	0.00	5	f	\N	2026-11-17 21:18:12.707796
32	10	Arreglo floral básico	Arreglo floral sencillo para regalo.	Flores de temporada y follaje.	550.00	\N	5	t	FM-PRD-0032	0.00	5	f	\N	2026-11-17 21:18:12.707796
33	10	Arreglo floral mediano	Arreglo floral de tamaño mediano para ocasiones especiales.	Flores de temporada, follaje y decoración.	750.00	\N	5	t	FM-PRD-0033	0.00	5	f	\N	2026-11-17 21:18:12.707796
34	10	Arreglo floral especial	Arreglo floral de mayor tamaño para ocasiones especiales.	Flores seleccionadas, follaje y decoración especial.	950.00	\N	3	t	FM-PRD-0034	0.00	5	f	\N	2026-11-17 21:18:12.707796
24	7	Manzana cubierta	Manzana cubierta con una preparación dulce.	Manzana, chocolate y toppings.	55.00	\N	13	t	FM-PRD-0024	0.00	5	f	\N	2026-11-17 21:18:12.707796
23	6	Gomitas con chamoy	Mezcla de gomitas preparada con chamoy y chile.	Gomitas, chamoy, chile y limón.	65.00	\N	18	t	FM-PRD-0023	0.00	5	f	\N	2026-11-17 21:18:12.707796
21	6	Mango Loco	Mango fresco preparado con chamoy y chile.	Mango, chamoy, chile y limón.	75.00	\N	6	t	FM-PRD-0021	0.00	5	f	\N	2026-11-17 21:18:12.707796
20	6	Vaso Fruta Madre	Vaso de fruta fresca preparado al momento.	Mango, sandía, melón, papaya y piña.	65.00	\N	8	t	FM-PRD-0020	0.00	5	f	\N	2026-11-17 21:18:12.707796
26	7	Mini fresas con chocolate	Fresas frescas cubiertas con chocolate.	Fresas, chocolate y toppings.	85.00	\N	12	t	FM-PRD-0026	0.00	5	f	\N	2026-11-17 21:18:12.707796
\.


--
-- Data for Name: reserva; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.reserva (id_reserva, id_evento, id_carrito, estado_reserva, costo_total) FROM stdin;
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario (id_usuario, nombre_completo, correo, telefono, contrasena_hash, rol, fecha_registro) FROM stdin;
2	carlos	oroberto593@gmail.com	9622585259	$2y$12$YfSy8qef4K9Jj8khnzYQI.PMcJ6B0aekcK94JppXd.G3wxXfznmhe	cliente	2026-10-13 16:46:10.719238
3	Luis Antonio Torres Escobar	luis.torres37@unach.mx	9622829600	$2y$12$Svmi0RYKNhhPnjV5Su0OY.KladPwQi3b0xuYNwG9mZ/Tj0cCZUHia	cliente	2026-11-11 23:11:28.120187
1	Roberto Carlos Orozco Avila	orobertocarlos80@gmail.com	9876543212	$2y$12$mkLk6ABzL3WyYmeZnicmNO32bFZAnuq33amKv7O40OpcyMk7BrQG.	admin	2026-10-13 12:33:12.074535
\.


--
-- Name: carrito_snack_id_carrito_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.carrito_snack_id_carrito_seq', 1, false);


--
-- Name: categoria_id_categoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categoria_id_categoria_seq', 11, true);


--
-- Name: detalle_pedido_id_detalle_pedido_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.detalle_pedido_id_detalle_pedido_seq', 21, true);


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direccion_id_direccion_seq', 12, true);


--
-- Name: entrega_id_entrega_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.entrega_id_entrega_seq', 12, true);


--
-- Name: evento_extra_id_evento_extra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evento_extra_id_evento_extra_seq', 1, false);


--
-- Name: evento_id_evento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.evento_id_evento_seq', 1, false);


--
-- Name: extra_id_extra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.extra_id_extra_seq', 4, true);


--
-- Name: notificacion_id_notificacion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notificacion_id_notificacion_seq', 1, false);


--
-- Name: pago_id_pago_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pago_id_pago_seq', 14, true);


--
-- Name: paquete_extra_id_paquete_extra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.paquete_extra_id_paquete_extra_seq', 4, true);


--
-- Name: paquete_id_paquete_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.paquete_id_paquete_seq', 1, true);


--
-- Name: paquete_producto_id_paquete_producto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.paquete_producto_id_paquete_producto_seq', 1, false);


--
-- Name: pedido_id_pedido_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pedido_id_pedido_seq', 14, true);


--
-- Name: producto_id_producto_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.producto_id_producto_seq', 34, true);


--
-- Name: reserva_id_reserva_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.reserva_id_reserva_seq', 1, false);


--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_id_usuario_seq', 3, true);


--
-- Name: carrito_snack carrito_snack_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carrito_snack
    ADD CONSTRAINT carrito_snack_pkey PRIMARY KEY (id_carrito);


--
-- Name: categoria categoria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_pkey PRIMARY KEY (id_categoria);


--
-- Name: detalle_pedido detalle_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_pkey PRIMARY KEY (id_detalle_pedido);


--
-- Name: direccion direccion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_pkey PRIMARY KEY (id_direccion);


--
-- Name: entrega entrega_id_pedido_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_pedido_key UNIQUE (id_pedido);


--
-- Name: entrega entrega_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_pkey PRIMARY KEY (id_entrega);


--
-- Name: evento_extra evento_extra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento_extra
    ADD CONSTRAINT evento_extra_pkey PRIMARY KEY (id_evento_extra);


--
-- Name: evento_extra evento_extra_unico; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento_extra
    ADD CONSTRAINT evento_extra_unico UNIQUE (id_evento, id_extra);


--
-- Name: evento evento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_pkey PRIMARY KEY (id_evento);


--
-- Name: extra extra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.extra
    ADD CONSTRAINT extra_pkey PRIMARY KEY (id_extra);


--
-- Name: notificacion notificacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notificacion
    ADD CONSTRAINT notificacion_pkey PRIMARY KEY (id_notificacion);


--
-- Name: pago pago_id_pedido_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_id_pedido_key UNIQUE (id_pedido);


--
-- Name: pago pago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_pkey PRIMARY KEY (id_pago);


--
-- Name: paquete_extra paquete_extra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_pkey PRIMARY KEY (id_paquete_extra);


--
-- Name: paquete paquete_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete
    ADD CONSTRAINT paquete_pkey PRIMARY KEY (id_paquete);


--
-- Name: paquete_producto paquete_producto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_pkey PRIMARY KEY (id_paquete_producto);


--
-- Name: pedido pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_pkey PRIMARY KEY (id_pedido);


--
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id_producto);


--
-- Name: reserva reserva_id_evento_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_evento_key UNIQUE (id_evento);


--
-- Name: reserva reserva_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_pkey PRIMARY KEY (id_reserva);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id_usuario);


--
-- Name: uq_producto_sku; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX uq_producto_sku ON public.producto USING btree (sku);


--
-- Name: detalle_pedido detalle_pedido_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: detalle_pedido detalle_pedido_id_producto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_id_producto_fkey FOREIGN KEY (id_producto) REFERENCES public.producto(id_producto);


--
-- Name: direccion direccion_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: entrega entrega_id_direccion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_direccion_fkey FOREIGN KEY (id_direccion) REFERENCES public.direccion(id_direccion);


--
-- Name: entrega entrega_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: evento_extra evento_extra_evento_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento_extra
    ADD CONSTRAINT evento_extra_evento_fk FOREIGN KEY (id_evento) REFERENCES public.evento(id_evento) ON DELETE CASCADE;


--
-- Name: evento_extra evento_extra_extra_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento_extra
    ADD CONSTRAINT evento_extra_extra_fk FOREIGN KEY (id_extra) REFERENCES public.extra(id_extra);


--
-- Name: evento evento_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: evento evento_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: notificacion notificacion_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notificacion
    ADD CONSTRAINT notificacion_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: pago pago_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: paquete_extra paquete_extra_id_extra_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_id_extra_fkey FOREIGN KEY (id_extra) REFERENCES public.extra(id_extra);


--
-- Name: paquete_extra paquete_extra_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: paquete_producto paquete_producto_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: paquete_producto paquete_producto_id_producto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_id_producto_fkey FOREIGN KEY (id_producto) REFERENCES public.producto(id_producto);


--
-- Name: pedido pedido_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: producto producto_id_categoria_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_id_categoria_fkey FOREIGN KEY (id_categoria) REFERENCES public.categoria(id_categoria);


--
-- Name: reserva reserva_id_carrito_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_carrito_fkey FOREIGN KEY (id_carrito) REFERENCES public.carrito_snack(id_carrito);


--
-- Name: reserva reserva_id_evento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_evento_fkey FOREIGN KEY (id_evento) REFERENCES public.evento(id_evento);


--
-- PostgreSQL database dump complete
--

\unrestrict dumbxOPn8Yw9JmnJ7Hd48Zy5YORDQYxApWvIrvCj0cvKuy5hi3BLbEhS4Q267AW


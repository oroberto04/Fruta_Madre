--
-- PostgreSQL database dump
--

\restrict wSPYEQWTDDmi42o0Y5IXapxt6e1TsbNv9edbFmgvs0OIZWcgHksdPsVhxaPSpoo

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
-- Name: carrito_snack; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.carrito_snack (
    id_carrito integer NOT NULL,
    codigo_inventario character varying(50),
    estado_fisico character varying(50)
);


--
-- Name: carrito_snack_id_carrito_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.carrito_snack_id_carrito_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: carrito_snack_id_carrito_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.carrito_snack_id_carrito_seq OWNED BY public.carrito_snack.id_carrito;


--
-- Name: categoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categoria (
    id_categoria integer NOT NULL,
    nombre_categoria character varying(255),
    descripcion text,
    activa boolean
);


--
-- Name: categoria_id_categoria_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categoria_id_categoria_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categoria_id_categoria_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categoria_id_categoria_seq OWNED BY public.categoria.id_categoria;


--
-- Name: detalle_pedido; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.detalle_pedido (
    id_detalle_pedido integer NOT NULL,
    id_pedido integer NOT NULL,
    id_producto integer NOT NULL,
    cantidad integer,
    precio_unitario numeric(10,2),
    subtotal_linea numeric(10,2)
);


--
-- Name: detalle_pedido_id_detalle_pedido_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.detalle_pedido_id_detalle_pedido_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: detalle_pedido_id_detalle_pedido_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.detalle_pedido_id_detalle_pedido_seq OWNED BY public.detalle_pedido.id_detalle_pedido;


--
-- Name: direccion; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.direccion_id_direccion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.direccion_id_direccion_seq OWNED BY public.direccion.id_direccion;


--
-- Name: entrega; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.entrega (
    id_entrega integer NOT NULL,
    id_pedido integer NOT NULL,
    id_direccion integer NOT NULL,
    fecha_estimada timestamp without time zone,
    estado_entrega character varying(50),
    notas_repartidor text
);


--
-- Name: entrega_id_entrega_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.entrega_id_entrega_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: entrega_id_entrega_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.entrega_id_entrega_seq OWNED BY public.entrega.id_entrega;


--
-- Name: evento; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: evento_id_evento_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.evento_id_evento_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: evento_id_evento_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.evento_id_evento_seq OWNED BY public.evento.id_evento;


--
-- Name: extra; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.extra (
    id_extra integer NOT NULL,
    nombre_extra character varying(255),
    descripcion text,
    precio_adicional numeric(10,2)
);


--
-- Name: extra_id_extra_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.extra_id_extra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: extra_id_extra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.extra_id_extra_seq OWNED BY public.extra.id_extra;


--
-- Name: notificacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notificacion (
    id_notificacion integer NOT NULL,
    id_usuario integer NOT NULL,
    titulo character varying(255),
    mensaje text,
    fecha_envio timestamp without time zone,
    estado_leida boolean
);


--
-- Name: notificacion_id_notificacion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notificacion_id_notificacion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notificacion_id_notificacion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notificacion_id_notificacion_seq OWNED BY public.notificacion.id_notificacion;


--
-- Name: pago; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pago (
    id_pago integer NOT NULL,
    id_pedido integer NOT NULL,
    metodo_pago character varying(50),
    estado_pago character varying(50),
    monto_pagado numeric(10,2),
    fecha_pago timestamp without time zone
);


--
-- Name: pago_id_pago_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pago_id_pago_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pago_id_pago_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pago_id_pago_seq OWNED BY public.pago.id_pago;


--
-- Name: paquete; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.paquete (
    id_paquete integer NOT NULL,
    nombre_paquete character varying(255),
    descripcion text,
    capacidad_personas integer,
    duracion_horas integer,
    precio_base numeric(10,2)
);


--
-- Name: paquete_extra; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.paquete_extra (
    id_paquete_extra integer NOT NULL,
    id_paquete integer NOT NULL,
    id_extra integer NOT NULL
);


--
-- Name: paquete_extra_id_paquete_extra_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.paquete_extra_id_paquete_extra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: paquete_extra_id_paquete_extra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.paquete_extra_id_paquete_extra_seq OWNED BY public.paquete_extra.id_paquete_extra;


--
-- Name: paquete_id_paquete_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.paquete_id_paquete_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: paquete_id_paquete_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.paquete_id_paquete_seq OWNED BY public.paquete.id_paquete;


--
-- Name: paquete_producto; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.paquete_producto (
    id_paquete_producto integer NOT NULL,
    id_paquete integer NOT NULL,
    id_producto integer NOT NULL,
    cantidad_incluida integer
);


--
-- Name: paquete_producto_id_paquete_producto_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.paquete_producto_id_paquete_producto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: paquete_producto_id_paquete_producto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.paquete_producto_id_paquete_producto_seq OWNED BY public.paquete_producto.id_paquete_producto;


--
-- Name: pedido; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pedido (
    id_pedido integer NOT NULL,
    id_usuario integer NOT NULL,
    fecha_pedido timestamp without time zone,
    estado_pedido character varying(50),
    subtotal numeric(10,2),
    total numeric(10,2)
);


--
-- Name: pedido_id_pedido_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pedido_id_pedido_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pedido_id_pedido_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pedido_id_pedido_seq OWNED BY public.pedido.id_pedido;


--
-- Name: producto; Type: TABLE; Schema: public; Owner: -
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
    activo boolean
);


--
-- Name: producto_id_producto_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.producto_id_producto_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: producto_id_producto_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.producto_id_producto_seq OWNED BY public.producto.id_producto;


--
-- Name: reserva; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reserva (
    id_reserva integer NOT NULL,
    id_evento integer NOT NULL,
    id_carrito integer NOT NULL,
    estado_reserva character varying(50),
    costo_total numeric(10,2)
);


--
-- Name: reserva_id_reserva_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reserva_id_reserva_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reserva_id_reserva_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reserva_id_reserva_seq OWNED BY public.reserva.id_reserva;


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: -
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


--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuario_id_usuario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuario_id_usuario_seq OWNED BY public.usuario.id_usuario;


--
-- Name: carrito_snack id_carrito; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_snack ALTER COLUMN id_carrito SET DEFAULT nextval('public.carrito_snack_id_carrito_seq'::regclass);


--
-- Name: categoria id_categoria; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categoria ALTER COLUMN id_categoria SET DEFAULT nextval('public.categoria_id_categoria_seq'::regclass);


--
-- Name: detalle_pedido id_detalle_pedido; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.detalle_pedido ALTER COLUMN id_detalle_pedido SET DEFAULT nextval('public.detalle_pedido_id_detalle_pedido_seq'::regclass);


--
-- Name: direccion id_direccion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.direccion ALTER COLUMN id_direccion SET DEFAULT nextval('public.direccion_id_direccion_seq'::regclass);


--
-- Name: entrega id_entrega; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entrega ALTER COLUMN id_entrega SET DEFAULT nextval('public.entrega_id_entrega_seq'::regclass);


--
-- Name: evento id_evento; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evento ALTER COLUMN id_evento SET DEFAULT nextval('public.evento_id_evento_seq'::regclass);


--
-- Name: extra id_extra; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.extra ALTER COLUMN id_extra SET DEFAULT nextval('public.extra_id_extra_seq'::regclass);


--
-- Name: notificacion id_notificacion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificacion ALTER COLUMN id_notificacion SET DEFAULT nextval('public.notificacion_id_notificacion_seq'::regclass);


--
-- Name: pago id_pago; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pago ALTER COLUMN id_pago SET DEFAULT nextval('public.pago_id_pago_seq'::regclass);


--
-- Name: paquete id_paquete; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete ALTER COLUMN id_paquete SET DEFAULT nextval('public.paquete_id_paquete_seq'::regclass);


--
-- Name: paquete_extra id_paquete_extra; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_extra ALTER COLUMN id_paquete_extra SET DEFAULT nextval('public.paquete_extra_id_paquete_extra_seq'::regclass);


--
-- Name: paquete_producto id_paquete_producto; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_producto ALTER COLUMN id_paquete_producto SET DEFAULT nextval('public.paquete_producto_id_paquete_producto_seq'::regclass);


--
-- Name: pedido id_pedido; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido ALTER COLUMN id_pedido SET DEFAULT nextval('public.pedido_id_pedido_seq'::regclass);


--
-- Name: producto id_producto; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto ALTER COLUMN id_producto SET DEFAULT nextval('public.producto_id_producto_seq'::regclass);


--
-- Name: reserva id_reserva; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reserva ALTER COLUMN id_reserva SET DEFAULT nextval('public.reserva_id_reserva_seq'::regclass);


--
-- Name: usuario id_usuario; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario ALTER COLUMN id_usuario SET DEFAULT nextval('public.usuario_id_usuario_seq'::regclass);


--
-- Name: carrito_snack carrito_snack_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.carrito_snack
    ADD CONSTRAINT carrito_snack_pkey PRIMARY KEY (id_carrito);


--
-- Name: categoria categoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_pkey PRIMARY KEY (id_categoria);


--
-- Name: detalle_pedido detalle_pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_pkey PRIMARY KEY (id_detalle_pedido);


--
-- Name: direccion direccion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_pkey PRIMARY KEY (id_direccion);


--
-- Name: entrega entrega_id_pedido_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_pedido_key UNIQUE (id_pedido);


--
-- Name: entrega entrega_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_pkey PRIMARY KEY (id_entrega);


--
-- Name: evento evento_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_pkey PRIMARY KEY (id_evento);


--
-- Name: extra extra_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.extra
    ADD CONSTRAINT extra_pkey PRIMARY KEY (id_extra);


--
-- Name: notificacion notificacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificacion
    ADD CONSTRAINT notificacion_pkey PRIMARY KEY (id_notificacion);


--
-- Name: pago pago_id_pedido_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_id_pedido_key UNIQUE (id_pedido);


--
-- Name: pago pago_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_pkey PRIMARY KEY (id_pago);


--
-- Name: paquete_extra paquete_extra_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_pkey PRIMARY KEY (id_paquete_extra);


--
-- Name: paquete paquete_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete
    ADD CONSTRAINT paquete_pkey PRIMARY KEY (id_paquete);


--
-- Name: paquete_producto paquete_producto_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_pkey PRIMARY KEY (id_paquete_producto);


--
-- Name: pedido pedido_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_pkey PRIMARY KEY (id_pedido);


--
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id_producto);


--
-- Name: reserva reserva_id_evento_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_evento_key UNIQUE (id_evento);


--
-- Name: reserva reserva_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_pkey PRIMARY KEY (id_reserva);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id_usuario);


--
-- Name: detalle_pedido detalle_pedido_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: detalle_pedido detalle_pedido_id_producto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.detalle_pedido
    ADD CONSTRAINT detalle_pedido_id_producto_fkey FOREIGN KEY (id_producto) REFERENCES public.producto(id_producto);


--
-- Name: direccion direccion_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: entrega entrega_id_direccion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_direccion_fkey FOREIGN KEY (id_direccion) REFERENCES public.direccion(id_direccion);


--
-- Name: entrega entrega_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entrega
    ADD CONSTRAINT entrega_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: evento evento_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: evento evento_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.evento
    ADD CONSTRAINT evento_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: notificacion notificacion_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificacion
    ADD CONSTRAINT notificacion_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: pago pago_id_pedido_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pago
    ADD CONSTRAINT pago_id_pedido_fkey FOREIGN KEY (id_pedido) REFERENCES public.pedido(id_pedido);


--
-- Name: paquete_extra paquete_extra_id_extra_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_id_extra_fkey FOREIGN KEY (id_extra) REFERENCES public.extra(id_extra);


--
-- Name: paquete_extra paquete_extra_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_extra
    ADD CONSTRAINT paquete_extra_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: paquete_producto paquete_producto_id_paquete_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_id_paquete_fkey FOREIGN KEY (id_paquete) REFERENCES public.paquete(id_paquete);


--
-- Name: paquete_producto paquete_producto_id_producto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.paquete_producto
    ADD CONSTRAINT paquete_producto_id_producto_fkey FOREIGN KEY (id_producto) REFERENCES public.producto(id_producto);


--
-- Name: pedido pedido_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pedido
    ADD CONSTRAINT pedido_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: producto producto_id_categoria_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_id_categoria_fkey FOREIGN KEY (id_categoria) REFERENCES public.categoria(id_categoria);


--
-- Name: reserva reserva_id_carrito_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_carrito_fkey FOREIGN KEY (id_carrito) REFERENCES public.carrito_snack(id_carrito);


--
-- Name: reserva reserva_id_evento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_id_evento_fkey FOREIGN KEY (id_evento) REFERENCES public.evento(id_evento);


--
-- PostgreSQL database dump complete
--

\unrestrict wSPYEQWTDDmi42o0Y5IXapxt6e1TsbNv9edbFmgvs0OIZWcgHksdPsVhxaPSpoo


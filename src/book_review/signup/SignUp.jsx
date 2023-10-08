import axios from 'axios';
import React, { useEffect, useState } from 'react';
import Compressor from 'compressorjs'; 
import { useCookies } from 'react-cookie';
import { useSelector, useDispatch } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form'; //個人的に「Formik」よりも「react-hook-form」の方がバリデーションの設定が少なくて良いと思う。
import { signIn } from '../../authSlice';
import { Header } from '../header/Header';
import { url } from '../../const';
import './signUp.scss';

export const SignUp = () => {
  const navigate = useNavigate(); // useNavigateフックを使用
	const { register, handleSubmit, formState: { errors } } = useForm(); // バリデーションのフォームを定義。
  const auth = useSelector((state) => state.auth.isSignIn);
  const dispatch = useDispatch();
  const [email, setEmail] = useState('');
  const [name, setName] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [ImgFile, setImgFile] = useState();     //「ImgFile」にはリサイズした画像が入る
  const [imgUrl, setImgUrl] = useState(''); //画面に表示させる画像のurlをセット
  const [, setCookie] = useCookies(['token']);
  const handleEmailChange = (e) => setEmail(e.target.value);
  const handleNameChange = (e) => setName(e.target.value);
  const handlePasswordChange = (e) => setPassword(e.target.value);

  const onSignUp = async () => {
    const data = {
      name: name,
      email: email,
      password: password
    }

    try {
      const res = await axios.post('http://localhost:8000/users', data);
      const token = res.data.token;
      setCookie('token', token, { maxAge : 86400 });//「86400」は「cookie」が有効な時間（秒数）。ちなみに「86400」は一日の秒数

      const formdata = new FormData();
      formdata.append('icon', ImgFile, ImgFile.name); // フィールド名を「icon」に指定しないと400エラーが起きる。（swaggerの仕様ではフィールド名を「icon」にしていたため）
 
      await axios.post(`${url}/uploads`, formdata, {
         headers: {
          authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      });
      dispatch(signIn());
      navigate('/');
    } catch (err) {
      setErrorMessage(`サインアップに失敗しました。 ${err}`);
    }
  }

    const handleIconUrlChange = (e) => { //画像が!MBより大きかったらリサイズする関数
        const file = e.target.files[0];
        const url = URL.createObjectURL(file);
        setImgUrl(url); // imgタグをusestateにセット　「usestateにurlをセットする」

        if (file.size > 1024 * 1024) { // 1MB以上の場合
        new Compressor(file, { //画像のリサイズする関数
          quality: 0.3,
          maxHeight: 10,
          maxWidth: 10,
          success(result) {
            setImgFile(result);
          },
          error(err) {
            console.error('画像の圧縮エラー:', err.message);
          },
        });
      }else{
        setImgFile(file);
      }
    };

    useEffect(()=>{
      if(auth) return navigate('/'); //「navegate」はレンダリング時に呼び出したらだめらしい。（「useEfect」内で呼び出そうとのエラーが出た）
    },[])

  return (
    <div>
      <Header />
      <main className="signup">
        <h2>新規作成</h2>
        <p className="error-message">{errorMessage}</p>
        <form onSubmit={handleSubmit(onSignUp)} className="signup-form">
          <label htmlFor="email">メールアドレス</label>
          <br />
          <input 
                type="email" 
                {...register("email", {required: true })}
                onChange={handleEmailChange} 
                className="email-input" 
          />
            {/* 何か所かにある以下のような記述はバリデーションエラーが発生したときに表示されるエラー文 */}
            <p>{errors.email?.type === 'required' && <b className='error-message'>※メールアドレスを入力してください</b> }</p>
          <br />
          <label>ユーザ名</label>
          <br />
          <input 
                type="text" 
                {...register("name", {required: true})} 
                onChange={handleNameChange} 
                className="name-input"
          />
            <p>{errors.name?.type === 'required' && <b className='error-message'>※アカウント名を入力してください。</b>}</p>
          <br />
          <label htmlFor="password">パスワード</label>
          <br />
          <input 
                type="password" 
                {...register("password", {required: true, minLength: {value: 5}})} 
                onChange={handlePasswordChange} 
                className="password-input" 
          />
            <p>{errors.password?.type === 'required' && <b className='error-message'>※パスワードを入力してください。</b>}</p>
            <p>{errors.password?.type === 'minLength' && <b className='error-message'>※パスワードは５文字以上で設定してください</b> }</p>
          <label>アイコン画像アップロード</label>
          <br />
          <input 
                 type="file"                  
                 onChange={handleIconUrlChange}
                 accept=".jpg, .png"
                 className='icon-uploads'
          />
          <img src={imgUrl} id="icon" alt="ユーザーのアイコン画像" />
          <br />
          <button type="submit"className="signup-button">
            作成
          </button>
        </form>
        <Link to="/login">ログイン画面へ</Link>
      </main>
    </div>
  );
}
